import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/widget/status_badge.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProjectCard extends StatelessWidget {
  final Project project;
  final VoidCallback? onTap;

  const ProjectCard({
    required this.project,
    this.onTap,
    super.key,
  });

  String? get _resolvedImageUrl {
    final String? rawImg = project.image?.trim();
    if (rawImg == null || rawImg.isEmpty) {
      return null;
    }
    if (rawImg.startsWith("http://") || rawImg.startsWith("https://")) {
      return rawImg;
    }
    final String clean = rawImg.startsWith("/") ? rawImg.substring(1) : rawImg;
    return "https://montanainvestama.com/$clean";
  }

  Widget _buildImagePlaceholder() {
    return Container(
      color: const Color(0xFF0F2C59).withValues(alpha: 0.08),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.precision_manufacturing_outlined,
              size: Dimensions.size35,
              color: const Color(0xFF0F2C59).withValues(alpha: 0.4),
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              project.category ?? "Alat Berat",
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImageHeader() {
    final String? imageUrl = _resolvedImageUrl;

    return ClipRRect(
      borderRadius: BorderRadius.circular(Dimensions.size10),
      child: Container(
        height: Dimensions.size100 + Dimensions.size40,
        width: double.infinity,
        color: const Color(0xFF0F2C59).withValues(alpha: 0.06),
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (imageUrl != null)
              Image.network(
                imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (
                  BuildContext context,
                  Object error,
                  StackTrace? stackTrace,
                ) {
                  return _buildImagePlaceholder();
                },
                loadingBuilder: (
                  BuildContext context,
                  Widget child,
                  ImageChunkEvent? loadingProgress,
                ) {
                  if (loadingProgress == null) {
                    return child;
                  }
                  return Center(
                    child: CircularProgressIndicator(
                      value: loadingProgress.expectedTotalBytes != null
                          ? loadingProgress.cumulativeBytesLoaded /
                              loadingProgress.expectedTotalBytes!
                          : null,
                      strokeWidth: 2,
                      color: const Color(0xFFC5A059),
                    ),
                  );
                },
              )
            else
              _buildImagePlaceholder(),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withValues(alpha: 0.45),
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.25),
                  ],
                ),
              ),
            ),
            Positioned(
              top: Dimensions.size10,
              left: Dimensions.size10,
              child: Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size5 + Dimensions.size3,
                  vertical: Dimensions.size4,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size5),
                    smoothness: 1,
                  ),
                  color: Colors.black.withValues(alpha: 0.6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.location_on,
                      size: Dimensions.size10,
                      color: Colors.white,
                    ),
                    SizedBox(width: Dimensions.size3),
                    Text(
                      project.city ?? "Indonesia",
                      style: TextStyle(
                        fontSize: Dimensions.text10,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Positioned(
              top: Dimensions.size10,
              right: Dimensions.size10,
              child: StatusBadge(status: project.status),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final double percent = project.fundingTarget > 0
        ? (project.fundingCollected / project.fundingTarget).clamp(0.0, 1.0)
        : 0.0;

    final String roi = (project.info?.returnRate != null &&
            project.info!.returnRate!.isNotEmpty)
        ? project.info!.returnRate!
        : "18-24% / thn";

    final String tenor =
        (project.info?.tenor != null && project.info!.tenor!.isNotEmpty)
            ? project.info!.tenor!
            : "24-36 bln";

    return InkWell(
      onTap: onTap ?? () => context.push("/projects/${project.id}"),
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size10),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.all(Dimensions.size15),
        decoration: ShapeDecoration(
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size10),
            smoothness: 1,
            side: BorderSide(color: AppColors.outline()),
          ),
          color: AppColors.surfaceContainerLowest(),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildImageHeader(),
            SizedBox(height: Dimensions.size10),
            Text(
              project.category ?? "Alat Berat",
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
            SizedBox(height: Dimensions.size4),
            Text(
              project.title ?? "-",
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: Dimensions.text14,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
                height: 1.3,
              ),
            ),
            SizedBox(height: Dimensions.size10),
            Row(
              children: [
                Container(
                  padding: EdgeInsets.symmetric(
                    horizontal: Dimensions.size5 + Dimensions.size3,
                    vertical: Dimensions.size4,
                  ),
                  decoration: ShapeDecoration(
                    shape: SmoothRectangleBorder(
                      borderRadius: BorderRadius.circular(Dimensions.size5),
                      smoothness: 1,
                    ),
                    color: const Color(0xFFC5A059).withValues(alpha: 0.12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.trending_up,
                        size: Dimensions.size10,
                        color: const Color(0xFFC5A059),
                      ),
                      SizedBox(width: Dimensions.size4),
                      Text(
                        roi,
                        style: TextStyle(
                          fontSize: Dimensions.text10,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFFC5A059),
                        ),
                      ),
                    ],
                  ),
                ),
                SizedBox(width: Dimensions.size10),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.schedule,
                      size: Dimensions.size10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                    SizedBox(width: Dimensions.size4),
                    Text(
                      tenor,
                      style: TextStyle(
                        fontSize: Dimensions.text10,
                        color: AppColors.onSurfaceVariant(),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            SizedBox(height: Dimensions.size10),
            ClipRRect(
              borderRadius: BorderRadius.circular(Dimensions.size5),
              child: LinearProgressIndicator(
                value: percent,
                backgroundColor: AppColors.outline(),
                valueColor: const AlwaysStoppedAnimation<Color>(
                  Color(0xFFC5A059),
                ),
                minHeight: Dimensions.size5,
              ),
            ),
            SizedBox(height: Dimensions.size10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    "${"collected_funding".tr()}: Rp ${Formats.spell(project.fundingCollected)}",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                ),
                Text(
                  "${(percent * 100).toStringAsFixed(1)}%",
                  style: TextStyle(
                    fontSize: Dimensions.text11,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0F2C59),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
