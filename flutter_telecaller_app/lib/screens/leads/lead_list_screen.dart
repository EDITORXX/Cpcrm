import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/lead_provider.dart';
import 'package:telecaller_crm/models/lead_model.dart';
import 'package:telecaller_crm/utils/helpers.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class LeadListScreen extends StatefulWidget {
  const LeadListScreen({super.key});

  @override
  State<LeadListScreen> createState() => _LeadListScreenState();
}

class _LeadListScreenState extends State<LeadListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final leadProvider = Provider.of<LeadProvider>(context, listen: false);
      if (leadProvider.leads.isEmpty && !leadProvider.isLoading) {
        leadProvider.loadLeads(refresh: true);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Leads'),
      ),
      body: Column(
        children: [
          _SearchBar(),
          Expanded(
            child: Consumer<LeadProvider>(
              builder: (context, leadProvider, _) {
                if (leadProvider.isLoading && leadProvider.leads.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (leadProvider.error != null && leadProvider.leads.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 48, color: Colors.red[300]),
                        const SizedBox(height: 16),
                        Text(
                          'Error: ${leadProvider.error}',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.red[600]),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => leadProvider.loadLeads(refresh: true),
                          icon: const Icon(Icons.refresh),
                          label: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (leadProvider.leads.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.people_outline, size: 48, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        const Text('No leads found'),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => leadProvider.loadLeads(refresh: true),
                          icon: const Icon(Icons.refresh),
                          label: const Text('Refresh'),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () => leadProvider.loadLeads(refresh: true),
                  child: GridView.builder(
                    padding: const EdgeInsets.all(16),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      childAspectRatio: 0.85,
                    ),
                    itemCount: leadProvider.leads.length,
                    itemBuilder: (context, index) {
                      return _LeadCard(lead: leadProvider.leads[index]);
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

class _SearchBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<LeadProvider>(
      builder: (context, leadProvider, _) {
        return Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            decoration: const InputDecoration(
              hintText: 'Search leads...',
              prefixIcon: Icon(Icons.search),
            ),
            onChanged: (value) {
              leadProvider.setSearchQuery(value);
            },
          ),
        );
      },
    );
  }
}

class _LeadCard extends StatelessWidget {
  final LeadModel lead;

  const _LeadCard({required this.lead});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              lead.name,
              style: Theme.of(context).textTheme.titleMedium,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            Text(
              lead.phone,
              style: Theme.of(context).textTheme.bodySmall,
            ),
            if (lead.email != null) ...[
              const SizedBox(height: 4),
              Text(
                lead.email!,
                style: Theme.of(context).textTheme.bodySmall,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const Spacer(),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                IconButton(
                  icon: const Icon(Icons.phone),
                  color: ThemeConfig.primaryColor,
                  onPressed: () => Helpers.makePhoneCall(lead.phone),
                ),
                IconButton(
                  icon: const Icon(Icons.chat),
                  color: ThemeConfig.successColor,
                  onPressed: () => Helpers.openWhatsApp(lead.phone),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
