class ProspectModel {
  final int id;
  final int leadId;
  final String customerName;
  final String phone;
  final String? budget;
  final String? preferredLocation;
  final String? size;
  final String purpose;
  final String? possession;
  final String remark;
  final String verificationStatus;
  final String? managerName;
  final int? managerId;
  final String? verifiedByName;
  final String? verifiedAt;
  final String? rejectionReason;
  final String? createdAt;
  final String? leadName;

  ProspectModel({
    required this.id,
    required this.leadId,
    required this.customerName,
    required this.phone,
    this.budget,
    this.preferredLocation,
    this.size,
    required this.purpose,
    this.possession,
    required this.remark,
    required this.verificationStatus,
    this.managerName,
    this.managerId,
    this.verifiedByName,
    this.verifiedAt,
    this.rejectionReason,
    this.createdAt,
    this.leadName,
  });

  factory ProspectModel.fromJson(Map<String, dynamic> json) {
    return ProspectModel(
      id: json['id'] ?? 0,
      leadId: json['lead_id'] ?? 0,
      customerName: json['customer_name'] ?? '',
      phone: json['phone'] ?? '',
      budget: json['budget'],
      preferredLocation: json['preferred_location'],
      size: json['size'],
      purpose: json['purpose'] ?? 'end_user',
      possession: json['possession'],
      remark: json['remark'] ?? '',
      verificationStatus: json['verification_status'] ?? 'pending',
      managerName: json['manager_name'],
      managerId: json['manager_id'],
      verifiedByName: json['verified_by_name'],
      verifiedAt: json['verified_at'],
      rejectionReason: json['rejection_reason'],
      createdAt: json['created_at'],
      leadName: json['lead_name'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'lead_id': leadId,
      'customer_name': customerName,
      'phone': phone,
      'budget': budget,
      'preferred_location': preferredLocation,
      'size': size,
      'purpose': purpose,
      'possession': possession,
      'remark': remark,
      'verification_status': verificationStatus,
      'manager_name': managerName,
      'manager_id': managerId,
      'verified_by_name': verifiedByName,
      'verified_at': verifiedAt,
      'rejection_reason': rejectionReason,
      'created_at': createdAt,
      'lead_name': leadName,
    };
  }
}

