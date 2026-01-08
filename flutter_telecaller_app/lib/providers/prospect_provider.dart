import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/prospect_model.dart';
import 'package:telecaller_crm/models/api_response_model.dart';

class ProspectProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<ProspectModel> _prospects = [];
  bool _isLoading = false;
  String? _error;
  String _currentFilter = 'pending';
  int _currentPage = 1;
  int _totalPages = 1;

  List<ProspectModel> get prospects => _prospects;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String get currentFilter => _currentFilter;

  Future<void> loadProspects({String? status, bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
    }

    _isLoading = true;
    _error = null;
    if (status != null) {
      _currentFilter = status;
    }
    notifyListeners();

    try {
      final response = await _apiService.get<Map<String, dynamic>>(
        ApiConfig.prospects,
        queryParameters: {
          'status': _currentFilter == 'all' ? null : _currentFilter,
          'page': _currentPage,
          'per_page': 20,
        },
        fromJson: (data) => data as Map<String, dynamic>,
      );

      if (response.success && response.data != null) {
        final data = response.data!['data'] as List?;
        final pagination = response.data!['pagination'] as Map<String, dynamic>?;

        if (data != null) {
          if (refresh) {
            _prospects = data.map((json) => ProspectModel.fromJson(json)).toList();
          } else {
            _prospects.addAll(data.map((json) => ProspectModel.fromJson(json)).toList());
          }

          if (pagination != null) {
            _currentPage = pagination['current_page'] ?? 1;
            _totalPages = pagination['last_page'] ?? 1;
          }
        }
        _error = null;
      } else {
        _error = response.message ?? 'Failed to load prospects';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createProspect({
    required int leadId,
    required String customerName,
    required String phone,
    String? budget,
    String? preferredLocation,
    String? size,
    required String purpose,
    String? possession,
    required String remark,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _apiService.post(
        ApiConfig.createProspect,
        data: {
          'lead_id': leadId,
          'customer_name': customerName,
          'phone': phone,
          'budget': budget,
          'preferred_location': preferredLocation,
          'size': size,
          'purpose': purpose,
          'possession': possession,
          'remark': remark,
        },
      );

      if (response.success) {
        await loadProspects(refresh: true);
        return true;
      } else {
        _error = response.message ?? 'Failed to create prospect';
        return false;
      }
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void filterProspects(String status) {
    _currentFilter = status;
    loadProspects(refresh: true);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

