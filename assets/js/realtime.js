/**
 * PT MONTANA GLOBAL INVESTAMA — REALTIME CLIENT ENGINE
 * Handles native Server-Sent Events (SSE) streaming with resilient polling fallback.
 * Automatically synchronizes DOM elements without reloading the page when admin updates content.
 */

(function (window, document) {
    'use strict';

    const CONFIG = {
        sseEndpoint: 'api/realtime/stream.php',
        checkEndpoint: 'api/realtime/check.php',
        pollIntervalMs: 25000,
        reconnectDelayMs: 3000,
        maxReconnectAttempts: 10
    };

    let eventSource = null;
    let lastKnownEventId = 0;
    let reconnectAttempts = 0;
    let fallbackPollTimer = null;
    let isConnected = false;

    // Resolve relative path to root API
    function getApiUrl(endpoint) {
        const root = document.querySelector('meta[name="mgi-root"]')?.getAttribute('content') || '';
        // If inside /portal-admin-mgi-gateway/, step up to root
        if (window.location.pathname.includes('/portal-admin-mgi-gateway/')) {
            return '../' + endpoint;
        }
        return (root ? root.replace(/\/$/, '') + '/' : '') + endpoint;
    }

    // Floating Realtime Sync Badge in UI
    function showSyncNotification(sectionName, action) {
        let badge = document.getElementById('mgiRealtimeSyncBadge');
        if (!badge) {
            badge = document.createElement('div');
            badge.id = 'mgiRealtimeSyncBadge';
            badge.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: #142563;
                color: #FFFFFF;
                border: 1px solid #C5A059;
                border-radius: 50px;
                padding: 8px 18px;
                font-size: 0.82rem;
                font-family: inherit;
                box-shadow: 0 8px 24px rgba(15, 44, 89, 0.25);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 99999;
                opacity: 0;
                transform: translateY(15px);
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                pointer-events: none;
            `;
            badge.innerHTML = `
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#C5A059; box-shadow:0 0 10px #C5A059; animation:mgiPulse 1.5s infinite;"></span>
                <span id="mgiRealtimeSyncText" style="font-weight: 500;">Konten diperbarui secara real-time</span>
            `;
            document.body.appendChild(badge);

            const style = document.createElement('style');
            style.textContent = `
                @keyframes mgiPulse {
                    0% { transform: scale(0.95); opacity: 0.8; }
                    50% { transform: scale(1.3); opacity: 1; }
                    100% { transform: scale(0.95); opacity: 0.8; }
                }
            `;
            document.head.appendChild(style);
        }

        const labelMap = {
            'hero': 'Hero Section',
            'features': 'Fitur & Pilar',
            'pricing': 'Paket Investasi',
            'projects': 'Portofolio Proyek',
            'testimonials': 'Testimoni Mitra',
            'faqs': 'FAQ Pertanyaan',
            'team': 'Dewan & Tim',
            'articles': 'Wawasan & Artikel',
            'settings': 'Pengaturan & Kontak'
        };

        const readableName = labelMap[sectionName] || sectionName;
        const textElem = document.getElementById('mgiRealtimeSyncText');
        if (textElem) {
            textElem.textContent = `${readableName} diperbarui langsung dari server`;
        }

        badge.style.opacity = '1';
        badge.style.transform = 'translateY(0)';

        clearTimeout(badge._hideTimeout);
        badge._hideTimeout = setTimeout(() => {
            badge.style.opacity = '0';
            badge.style.transform = 'translateY(15px)';
        }, 3500);
    }

    // Sound chime for admin incoming alerts
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880.00, ctx.currentTime + 0.15); // A5
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.start();
            osc.stop(ctx.currentTime + 0.45);
        } catch (e) {
            // Audio context blocked or unsupported
        }
    }

    // Handler for content updates on public landing pages
    function handleContentUpdated(data) {
        const section = data.section || '';
        showSyncNotification(section, data.action || 'update');

        switch (section) {
            case 'hero':
                if (typeof window.loadHeroSection === 'function') {
                    window.loadHeroSection();
                }
                break;
            case 'features':
                if (typeof window.loadPilars === 'function') window.loadPilars();
                if (typeof window.loadValueProps === 'function') window.loadValueProps();
                break;
            case 'pricing':
                if (typeof window.loadPricingTiers === 'function') {
                    window.loadPricingTiers();
                }
                break;
            case 'projects':
                if (typeof window.loadProjectsPreview === 'function') window.loadProjectsPreview();
                if (typeof window.loadProjects === 'function') window.loadProjects();
                if (typeof window.loadProjectDetail === 'function') window.loadProjectDetail();
                break;
            case 'testimonials':
                if (typeof window.loadTestimonials === 'function') {
                    window.loadTestimonials();
                }
                break;
            case 'faqs':
                if (typeof window.loadFaqs === 'function') {
                    window.loadFaqs();
                }
                break;
            case 'team':
                if (typeof window.loadTeamMembers === 'function') {
                    window.loadTeamMembers();
                }
                break;
            case 'articles':
                if (typeof window.loadArticles === 'function') {
                    window.loadArticles();
                }
                break;
            case 'settings':
                if (typeof window.syncPublicSettings === 'function') {
                    window.syncPublicSettings();
                }
                break;
        }

        // Trigger custom global event for extensible components
        document.dispatchEvent(new CustomEvent('mgi:realtime_content_updated', { detail: data }));
    }

    // Handler for admin notifications (Incoming leads, inquiries, subscribers)
    function handleAdminEvent(eventType, data) {
        playNotificationSound();

        const titleMap = {
            'new_inquiry': 'Pesan Masuk Baru (Kontak)',
            'new_subscriber': 'Subscriber Newsletter Baru',
            'new_lead': 'Calon Investor Baru (Lead)'
        };

        const title = titleMap[eventType] || 'Notifikasi Sistem Baru';
        const payload = data.payload || {};
        const detail = payload.full_name || payload.name || payload.email || 'Data interaksi baru';

        // Check if admin is on the relevant table page and reload
        if (eventType === 'new_inquiry' && typeof window.loadInquiries === 'function') {
            window.loadInquiries();
        } else if (eventType === 'new_subscriber' && typeof window.loadSubscribers === 'function') {
            window.loadSubscribers();
        } else if (eventType === 'new_lead' && typeof window.loadLeads === 'function') {
            window.loadLeads();
        }

        // Show floating admin toast
        showAdminAlert(title, detail, eventType);
        document.dispatchEvent(new CustomEvent('mgi:admin_event', { detail: { eventType, data } }));
    }

    function showAdminAlert(title, message, type) {
        let container = document.getElementById('mgiAdminToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'mgiAdminToastContainer';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: #FFFFFF;
            border-left: 4px solid #142563;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 8px;
            padding: 14px 18px;
            min-width: 300px;
            max-width: 420px;
            pointer-events: auto;
            transform: translateX(50px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
        `;
        toast.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                <div>
                    <div style="font-weight:700; color:#142563; font-size:0.9rem; margin-bottom:2px;">
                        <span style="color:#C5A059;">●</span> ${title}
                    </div>
                    <div style="color:#475569; font-size:0.83rem;">${message}</div>
                </div>
                <button type="button" style="background:none;border:none;color:#94A3B8;cursor:pointer;font-size:1.1rem;line-height:1;" onclick="this.closest('div').parentElement.remove()">&times;</button>
            </div>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 350);
        }, 5500);
    }

    // Connect Server-Sent Events (SSE)
    function initSSE() {
        if (!window.EventSource) {
            initFallbackPolling();
            return;
        }

        try {
            if (eventSource) {
                eventSource.close();
            }

            const url = getApiUrl(CONFIG.sseEndpoint) + (lastKnownEventId > 0 ? `?last_id=${lastKnownEventId}` : '');
            eventSource = new EventSource(url);

            eventSource.addEventListener('connected', function (e) {
                isConnected = true;
                reconnectAttempts = 0;
                stopFallbackPolling();
            });

            eventSource.addEventListener('content_updated', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.id) lastKnownEventId = data.id;
                    handleContentUpdated(data);
                } catch (err) {
                    console.error('[MGI Realtime] Failed parsing content_updated event:', err);
                }
            });

            eventSource.addEventListener('new_inquiry', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.id) lastKnownEventId = data.id;
                    handleAdminEvent('new_inquiry', data);
                } catch (err) {}
            });

            eventSource.addEventListener('new_subscriber', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.id) lastKnownEventId = data.id;
                    handleAdminEvent('new_subscriber', data);
                } catch (err) {}
            });

            eventSource.addEventListener('new_lead', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.id) lastKnownEventId = data.id;
                    handleAdminEvent('new_lead', data);
                } catch (err) {}
            });

            eventSource.addEventListener('analytics_ping', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.id) lastKnownEventId = data.id;
                    if (typeof window.incrementLiveAnalyticsCounter === 'function') {
                        window.incrementLiveAnalyticsCounter(data);
                    }
                } catch (err) {}
            });

            eventSource.addEventListener('heartbeat_reconnect', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.last_event_id) lastKnownEventId = data.last_event_id;
                } catch (err) {}
            });

            eventSource.onerror = function () {
                isConnected = false;
                eventSource.close();
                eventSource = null;

                reconnectAttempts++;
                if (reconnectAttempts > CONFIG.maxReconnectAttempts) {
                    initFallbackPolling();
                } else {
                    setTimeout(initSSE, CONFIG.reconnectDelayMs);
                }
            };

        } catch (e) {
            initFallbackPolling();
        }
    }

    // Fallback: Lightweight Polling (Interval 25s) with ETag check
    function initFallbackPolling() {
        if (fallbackPollTimer) return;

        fallbackPollTimer = setInterval(pollServer, CONFIG.pollIntervalMs);
        pollServer();
    }

    function stopFallbackPolling() {
        if (fallbackPollTimer) {
            clearInterval(fallbackPollTimer);
            fallbackPollTimer = null;
        }
    }

    function pollServer() {
        const url = getApiUrl(CONFIG.checkEndpoint) + `?since_id=${lastKnownEventId}`;
        fetch(url, {
            cache: 'no-cache',
            headers: { 'Accept': 'application/json' }
        })
        .then(res => {
            if (res.status === 304) {
                return null; // Not modified, zero changes
            }
            return res.json();
        })
        .then(json => {
            if (!json || !json.data) return;
            const resData = json.data;
            if (resData.last_event_id) {
                lastKnownEventId = resData.last_event_id;
            }

            if (resData.events && resData.events.length > 0) {
                resData.events.forEach(ev => {
                    if (ev.event_type === 'content_updated') {
                        handleContentUpdated(ev);
                    } else if (['new_inquiry', 'new_subscriber', 'new_lead'].includes(ev.event_type)) {
                        handleAdminEvent(ev.event_type, ev);
                    }
                });
            }
        })
        .catch(err => {
            // Network failure: silent retry next cycle
        });
    }

    // Start Realtime on DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSSE);
    } else {
        initSSE();
    }

    // Handle Page Visibility Change (pause/resume cleanly on tab backgrounding)
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && !isConnected) {
            initSSE();
        }
    });

    // Expose MGI Realtime API to window
    window.MGI_REALTIME = {
        init: initSSE,
        poll: pollServer,
        getStatus: () => ({ isConnected, lastKnownEventId, reconnectAttempts })
    };

})(window, document);
