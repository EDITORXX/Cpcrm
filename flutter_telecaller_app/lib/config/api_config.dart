/// API Configuration
/// 
/// To change the server URL for deployment:
/// 1. Update the baseUrl constant below with your server URL
/// 2. Format: 'https://yourdomain.com/api' or 'http://your-ip:port/api'
/// 3. Rebuild the app: flutter clean && flutter pub get && flutter build apk --release
/// 
/// Examples:
/// - Production: 'https://crm.yourdomain.com/api'
/// - Local Development: 'http://192.168.1.100:8000/api'
/// - Localhost: 'http://localhost:8000/api'
class ApiConfig {
  // ============================================
  // SERVER URL CONFIGURATION
  // ============================================
  // Change this URL to match your deployment server
  // IMPORTANT: Include the full URL with protocol (http:// or https://)
  // and the /api path at the end
  // ============================================
  static const String baseUrl = 'https://crm.bihtech.com/api'; // CHANGE THIS FOR YOUR DEPLOYMENT
  
  // ============================================
  // END CONFIGURATION
  // ============================================
  
  static const String telecallerBaseUrl = '$baseUrl/telecaller';
  
  // General Authentication Endpoints (for all users)
  static const String login = '$baseUrl/login';
  static const String logout = '$baseUrl/logout';
  static const String me = '$baseUrl/me';
  
  // Telecaller-specific Authentication Endpoints (for backward compatibility)
  static const String telecallerLogin = '$baseUrl/telecaller/login';
  static const String telecallerLogout = '$telecallerBaseUrl/logout';
  static const String whoami = '$telecallerBaseUrl/whoami';
  
  // Profile Endpoints
  static const String profile = '$telecallerBaseUrl/profile';
  static const String updateProfile = '$telecallerBaseUrl/profile';
  static const String uploadProfilePicture = '$telecallerBaseUrl/profile/picture';
  static const String changePassword = '$telecallerBaseUrl/profile/password';
  
  // Dashboard Endpoints
  static const String dashboard = '$telecallerBaseUrl/dashboard';
  static const String stats = '$telecallerBaseUrl/stats';
  
  // Tasks Endpoints
  static const String tasks = '$telecallerBaseUrl/tasks';
  static const String taskStats = '$telecallerBaseUrl/tasks/stats';
  static String recordOutcome(int taskId) => '$telecallerBaseUrl/tasks/$taskId/outcome';
  
  // Leads Endpoints
  static const String leads = '$telecallerBaseUrl/leads';
  
  // Prospects Endpoints
  static const String prospects = '$telecallerBaseUrl/prospects';
  static const String createProspect = '$telecallerBaseUrl/prospects/create';
  
  // Call Tracking Endpoints
  static const String callLogs = '$telecallerBaseUrl/call-logs';
  static const String callStatistics = '$telecallerBaseUrl/call-statistics';
  static const String saveCallLog = '$telecallerBaseUrl/call-logs';
}

