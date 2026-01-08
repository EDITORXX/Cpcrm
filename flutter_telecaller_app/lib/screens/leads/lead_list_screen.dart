import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/lead_provider.dart';
import 'package:telecaller_crm/models/lead_model.dart';
import 'package:telecaller_crm/utils/helpers.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class LeadListScreen extends StatelessWidget {
  const LeadListScreen({super.key});

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

                if (leadProvider.error != null) {
                  return Center(
                    child: Text('Error: ${leadProvider.error}'),
                  );
                }

                if (leadProvider.leads.isEmpty) {
                  return const Center(child: Text('No leads found'));
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

