class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? profilePicture;
  final String role; // Role slug (admin, crm, sales_manager, etc.)
  final String? roleName; // Role display name
  final String? manager;
  final String? createdAt;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.profilePicture,
    required this.role,
    this.roleName,
    this.manager,
    this.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    // Handle role - can be string or object
    String roleSlug = 'sales_executive'; // Default
    String? roleDisplayName;
    
    if (json['role'] != null) {
      if (json['role'] is String) {
        // If role is already a string
        roleSlug = json['role'];
      } else if (json['role'] is Map) {
        // If role is an object with slug and name
        final roleObj = json['role'] as Map<String, dynamic>;
        roleSlug = roleObj['slug'] ?? roleObj['name'] ?? 'sales_executive';
        roleDisplayName = roleObj['name'];
      }
    }
    
    // Handle manager - can be string or object
    String? managerName;
    if (json['manager'] != null) {
      if (json['manager'] is String) {
        managerName = json['manager'];
      } else if (json['manager'] is Map) {
        final managerObj = json['manager'] as Map<String, dynamic>;
        managerName = managerObj['name'];
      }
    }
    
    return UserModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      profilePicture: json['profile_picture'],
      role: roleSlug,
      roleName: roleDisplayName,
      manager: managerName,
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'profile_picture': profilePicture,
      'role': role,
      'role_name': roleName,
      'manager': manager,
      'created_at': createdAt,
    };
  }
  
  // Helper methods for role checking
  bool get isAdmin => role == 'admin';
  bool get isCrm => role == 'crm';
  bool get isSalesManager => role == 'sales_manager';
  bool get isSalesExecutive => role == 'sales_executive';
  bool get isTelecaller => role == 'sales_executive' || role == 'telecaller';
  bool get isHrManager => role == 'hr_manager';
  bool get isFinanceManager => role == 'finance_manager';
}

