class LeadModel {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? city;
  final String? state;
  final String status;
  final String? lastContactedAt;
  final String? nextFollowupAt;
  final String? createdAt;
  final String? assignedAt;

  LeadModel({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.city,
    this.state,
    required this.status,
    this.lastContactedAt,
    this.nextFollowupAt,
    this.createdAt,
    this.assignedAt,
  });

  factory LeadModel.fromJson(Map<String, dynamic> json) {
    return LeadModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? '-',
      phone: json['phone'] ?? '-',
      email: json['email'],
      city: json['city'],
      state: json['state'],
      status: json['status'] ?? 'new',
      lastContactedAt: json['last_contacted_at'],
      nextFollowupAt: json['next_followup_at'],
      createdAt: json['created_at'],
      assignedAt: json['assigned_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'email': email,
      'city': city,
      'state': state,
      'status': status,
      'last_contacted_at': lastContactedAt,
      'next_followup_at': nextFollowupAt,
      'created_at': createdAt,
      'assigned_at': assignedAt,
    };
  }
}

