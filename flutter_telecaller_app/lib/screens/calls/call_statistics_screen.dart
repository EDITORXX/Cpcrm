import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/call_tracking_provider.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class CallStatisticsScreen extends StatelessWidget {
  const CallStatisticsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Call Statistics'),
      ),
      body: Consumer<CallTrackingProvider>(
        builder: (context, callProvider, _) {
          if (callProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      children: [
                        Text(
                          "Today's Overview",
                          style: Theme.of(context).textTheme.headlineMedium,
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _StatItem(
                              label: 'Total Calls',
                              value: callProvider.todayTotalCalls.toString(),
                            ),
                            _StatItem(
                              label: 'Talking Time',
                              value: callProvider.formattedTalkingTime,
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _StatItem(
                              label: 'Incoming',
                              value: callProvider.todayIncomingCalls.toString(),
                            ),
                            _StatItem(
                              label: 'Outgoing',
                              value: callProvider.todayOutgoingCalls.toString(),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'Call History',
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                const SizedBox(height: 16),
                if (callProvider.callLogs.isEmpty)
                  const Center(child: Text('No call logs found'))
                else
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: callProvider.callLogs.length,
                    itemBuilder: (context, index) {
                      final log = callProvider.callLogs[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: ListTile(
                          leading: Icon(
                            log.callType == 'incoming'
                                ? Icons.phone_callback
                                : Icons.phone_forwarded,
                            color: ThemeConfig.primaryColor,
                          ),
                          title: Text(log.phoneNumber),
                          subtitle: Text(
                            '${log.startTime.day}/${log.startTime.month}/${log.startTime.year} ${log.startTime.hour}:${log.startTime.minute.toString().padLeft(2, '0')}',
                          ),
                          trailing: Text(log.formattedDuration),
                        ),
                      );
                    },
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;

  const _StatItem({
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          value,
          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                color: ThemeConfig.primaryColor,
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: Theme.of(context).textTheme.bodySmall,
        ),
      ],
    );
  }
}

