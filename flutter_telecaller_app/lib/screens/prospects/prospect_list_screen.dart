import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/prospect_provider.dart';
import 'package:telecaller_crm/models/prospect_model.dart';
import 'package:telecaller_crm/utils/helpers.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class ProspectListScreen extends StatelessWidget {
  const ProspectListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Verification Pending'),
      ),
      body: Column(
        children: [
          _FilterTabs(),
          Expanded(
            child: Consumer<ProspectProvider>(
              builder: (context, prospectProvider, _) {
                if (prospectProvider.isLoading &&
                    prospectProvider.prospects.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (prospectProvider.error != null) {
                  return Center(
                    child: Text('Error: ${prospectProvider.error}'),
                  );
                }

                if (prospectProvider.prospects.isEmpty) {
                  return const Center(child: Text('No prospects found'));
                }

                return RefreshIndicator(
                  onRefresh: () =>
                      prospectProvider.loadProspects(refresh: true),
                  child: ListView.builder(
                    itemCount: prospectProvider.prospects.length,
                    itemBuilder: (context, index) {
                      return _ProspectCard(
                        prospect: prospectProvider.prospects[index],
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterTabs extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<ProspectProvider>(
      builder: (context, prospectProvider, _) {
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _TabButton(
                  label: 'Pending',
                  isActive: prospectProvider.currentFilter == 'pending',
                  onTap: () => prospectProvider.filterProspects('pending'),
                ),
                const SizedBox(width: 8),
                _TabButton(
                  label: 'Approved',
                  isActive: prospectProvider.currentFilter == 'approved',
                  onTap: () => prospectProvider.filterProspects('approved'),
                ),
                const SizedBox(width: 8),
                _TabButton(
                  label: 'Rejected',
                  isActive: prospectProvider.currentFilter == 'rejected',
                  onTap: () => prospectProvider.filterProspects('rejected'),
                ),
                const SizedBox(width: 8),
                _TabButton(
                  label: 'All',
                  isActive: prospectProvider.currentFilter == 'all',
                  onTap: () => prospectProvider.filterProspects('all'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _TabButton extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _TabButton({
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: isActive
            ? ThemeConfig.primaryColor
            : Colors.grey[300],
        foregroundColor: isActive ? Colors.white : Colors.black87,
      ),
      child: Text(label),
    );
  }
}

class _ProspectCard extends StatelessWidget {
  final ProspectModel prospect;

  const _ProspectCard({required this.prospect});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        prospect.customerName,
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        prospect.phone,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(prospect.verificationStatus),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    prospect.verificationStatus.toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            if (prospect.budget != null) ...[
              const SizedBox(height: 8),
              Text('Budget: ${prospect.budget}'),
            ],
            if (prospect.managerName != null) ...[
              const SizedBox(height: 4),
              Text('Manager: ${prospect.managerName}'),
            ],
            if (prospect.rejectionReason != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.red[50],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'Rejection Reason: ${prospect.rejectionReason}',
                  style: TextStyle(color: Colors.red[900]),
                ),
              ),
            ],
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => Helpers.openWhatsApp(prospect.phone),
                icon: const Icon(Icons.chat),
                label: const Text('WhatsApp'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: ThemeConfig.successColor,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return ThemeConfig.warningColor;
      case 'approved':
      case 'verified':
        return ThemeConfig.successColor;
      case 'rejected':
        return ThemeConfig.errorColor;
      default:
        return Colors.grey;
    }
  }
}

