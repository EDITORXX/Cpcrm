class ApiConfig {
  // Update this with your actual server URL
  // For same network access, use your local IP address instead of localhost
  static const String baseUrl = 'http://192.168.1.7:8000/api';
  static const String telecallerBaseUrl = '$baseUrl/telecaller';
  
  // Authentication Endpoints
  static const String login = '$baseUrl/telecaller/login';
  static const String logout = '$telecallerBaseUrl/logout';
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

