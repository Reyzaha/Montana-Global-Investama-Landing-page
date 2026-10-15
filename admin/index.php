<?php
/**
 * Admin Route Redirector
 * PT Montana Global Investama
 * 
 * Safely redirects legacy/short /admin access to the dedicated admin gateway login.
 */
header('Location: /portal-admin-mgi-gateway/login.php', true, 302);
exit;
