class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? profilePicture;
  final String role;
  final String? manager;
  final String? createdAt;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.profilePicture,
    required this.role,
    this.manager,
    this.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      profilePicture: json['profile_picture'],
      role: json['role'] ?? 'Telecaller',
      manager: json['manager'],
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
      'manager': manager,
      'created_at': createdAt,
    };
  }
}

