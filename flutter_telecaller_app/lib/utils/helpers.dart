import 'package:url_launcher/url_launcher.dart';

class Helpers {
  static Future<void> makePhoneCall(String phoneNumber) async {
    final uri = Uri.parse('tel:$phoneNumber');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  static Future<void> openWhatsApp(String phoneNumber) async {
    // Remove any non-digit characters
    final cleanNumber = phoneNumber.replaceAll(RegExp(r'[^\d]'), '');
    final uri = Uri.parse('https://wa.me/$cleanNumber');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  static String formatDate(String? dateString) {
    if (dateString == null) return '-';
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateString;
    }
  }

  static String formatDateTime(String? dateString) {
    if (dateString == null) return '-';
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return dateString;
    }
  }

  static String getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return '#f59e0b'; // Orange
      case 'completed':
        return '#10b981'; // Green
      case 'rescheduled':
        return '#667eea'; // Purple
      case 'approved':
      case 'verified':
        return '#10b981'; // Green
      case 'rejected':
        return '#ef4444'; // Red
      default:
        return '#6b7280'; // Gray
    }
  }
}

