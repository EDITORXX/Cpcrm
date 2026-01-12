#!/usr/bin/env python3
"""
Generate a comprehensive Verification Flow Diagram in PDF format
for Laravel CRM System - Prospect Verification Process
"""

from matplotlib import pyplot as plt
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch, Circle, Rectangle
import matplotlib.patches as mpatches
from matplotlib.backends.backend_pdf import PdfPages
import numpy as np

# Create PDF
pdf = PdfPages('Verification_Flow_Diagram.pdf')

# Create figure with larger size for better readability
fig = plt.figure(figsize=(20, 28))
ax = fig.add_subplot(111)
ax.set_xlim(0, 100)
ax.set_ylim(0, 140)
ax.axis('off')

# Colors
color_telecaller = '#16A085'  # Teal
color_prospect = '#3498DB'  # Blue
color_manager = '#F39C12'  # Orange
color_pending = '#FF6347'  # Tomato Red
color_verified = '#32CD32'  # Lime Green
color_rejected = '#DC143C'  # Crimson
color_followup = '#9370DB'  # Medium Purple
color_task = '#FFD700'  # Gold
color_event = '#9B59B6'  # Purple

# Title
ax.text(50, 135, 'PROSPECT VERIFICATION FLOW - DETAILED DIAGRAM', 
        ha='center', va='center', fontsize=24, fontweight='bold',
        bbox=dict(boxstyle='round,pad=1', facecolor='#2C3E50', edgecolor='black', linewidth=2))

# Section 1: Initial Stage - Telecaller Interaction
ax.text(50, 128, 'STEP 1: TELECALLER MARKS LEAD AS INTERESTED', 
        ha='center', va='center', fontsize=18, fontweight='bold', color='#2C3E50',
        bbox=dict(boxstyle='round,pad=0.5', facecolor=color_telecaller, edgecolor='black', linewidth=2))

# Telecaller action box
telecaller_box = FancyBboxPatch((35, 121), 30, 4,
                                boxstyle='round,pad=0.5',
                                facecolor=color_telecaller, edgecolor='black', linewidth=2)
ax.add_patch(telecaller_box)
ax.text(50, 123, 'TELECALLER\nMarks Lead as "Interested"', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Form data collected
form_data_box = FancyBboxPatch((5, 116), 20, 3,
                               boxstyle='round,pad=0.3',
                               facecolor='#E8F4F8', edgecolor='#3498DB', linewidth=1.5)
ax.add_patch(form_data_box)
form_text = 'Form Data:\n• Budget\n• Location\n• Size\n• Purpose\n• Possession\n• Remark'
ax.text(15, 117.5, form_text, ha='left', va='center', fontsize=9, fontweight='bold')

# Arrow to prospect creation
ax.arrow(50, 120, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 2: Prospect Creation
ax.text(50, 113, 'STEP 2: PROSPECT CREATED', 
        ha='center', va='center', fontsize=16, fontweight='bold', color='#2C3E50')

prospect_box = FancyBboxPatch((35, 108), 30, 4,
                              boxstyle='round,pad=0.5',
                              facecolor=color_prospect, edgecolor='black', linewidth=2)
ax.add_patch(prospect_box)
ax.text(50, 110, 'PROSPECT CREATED\nStatus: pending_verification', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Prospect details
prospect_details = [
    ('Customer Name', 'Phone', 'Budget'),
    ('Preferred Location', 'Size', 'Purpose'),
    ('Assigned Manager ID', 'Lead Score', 'Telecaller Remark')
]

detail_y = 104
for row in prospect_details:
    detail_text = ' | '.join(row)
    ax.text(50, detail_y, detail_text, ha='center', va='center',
            fontsize=8, style='italic', color='#2C3E50')
    detail_y -= 0.8

# Arrow to event
ax.arrow(50, 106, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 3: Event Fired
ax.text(50, 100, 'STEP 3: EVENT FIRED', 
        ha='center', va='center', fontsize=16, fontweight='bold', color='#2C3E50')

event_box = FancyBboxPatch((35, 96), 30, 3,
                           boxstyle='round,pad=0.5',
                           facecolor=color_event, edgecolor='black', linewidth=2)
ax.add_patch(event_box)
ax.text(50, 97.5, 'ProspectSentForVerification Event\nFired', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Arrow to listener
ax.arrow(50, 95, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 4: Listener Creates Task
ax.text(50, 89, 'STEP 4: LISTENER CREATES VERIFICATION TASK', 
        ha='center', va='center', fontsize=16, fontweight='bold', color='#2C3E50')

listener_box = FancyBboxPatch((30, 84), 40, 3,
                              boxstyle='round,pad=0.5',
                              facecolor='#E67E22', edgecolor='black', linewidth=2)
ax.add_patch(listener_box)
ax.text(50, 85.5, 'CreateManagerVerificationCallTask Listener', ha='center', va='center',
        fontsize=11, fontweight='bold', color='white')

# Task details
task_details_box = FancyBboxPatch((5, 78), 25, 4,
                                  boxstyle='round,pad=0.3',
                                  facecolor='#FFF9E6', edgecolor=color_task, linewidth=1.5)
ax.add_patch(task_details_box)
task_text = 'Task Created:\n• Type: phone_call\n• Assigned: Manager\n• Scheduled: +10 mins\n• Title: "Verify prospect..."'
ax.text(17.5, 80, task_text, ha='left', va='center', fontsize=9, fontweight='bold')

# Arrow to manager task list
ax.arrow(50, 82, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 5: Manager Receives Task
ax.text(50, 76, 'STEP 5: SALES MANAGER RECEIVES TASK', 
        ha='center', va='center', fontsize=16, fontweight='bold', color='#2C3E50',
        bbox=dict(boxstyle='round,pad=0.5', facecolor=color_manager, edgecolor='black', linewidth=2))

manager_box = FancyBboxPatch((35, 71), 30, 4,
                             boxstyle='round,pad=0.5',
                             facecolor=color_manager, edgecolor='black', linewidth=2)
ax.add_patch(manager_box)
ax.text(50, 73, 'SALES MANAGER\nViews Task in Dashboard', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Task list details
task_list_box = FancyBboxPatch((65, 68), 20, 5,
                               boxstyle='round,pad=0.3',
                               facecolor='#FFF9E6', edgecolor=color_task, linewidth=1.5)
ax.add_patch(task_list_box)
task_list_text = 'Task List Shows:\n• Customer Name\n• Phone Number\n• Prospect Details\n• Scheduled Time\n• Status: Pending'
ax.text(75, 70.5, task_list_text, ha='left', va='center', fontsize=9, fontweight='bold')

# Arrow to decision point
ax.arrow(50, 70, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 6: Manager Makes Verification Call
ax.text(50, 64, 'STEP 6: MANAGER MAKES VERIFICATION CALL', 
        ha='center', va='center', fontsize=16, fontweight='bold', color='#2C3E50')

call_box = FancyBboxPatch((35, 59), 30, 4,
                          boxstyle='round,pad=0.5',
                          facecolor='#E74C3C', edgecolor='black', linewidth=2)
ax.add_patch(call_box)
ax.text(50, 61, 'MANAGER CALLS CUSTOMER\nVerifies Information', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Arrow to decision
ax.arrow(50, 58, 0, -4, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 7: Decision Point - Verification Outcome
ax.text(50, 51, 'STEP 7: VERIFICATION OUTCOME', 
        ha='center', va='center', fontsize=18, fontweight='bold', color='#2C3E50',
        bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

# Decision diamond
decision_points = [
    ('VERIFIED\n(Hot/Warm/Cold/Junk)', color_verified, 20, 45),
    ('FOLLOW-UP\n(Follow-up Date)', color_followup, 50, 45),
    ('REJECTED\n(Rejection Reason)', color_rejected, 80, 45),
]

for decision, color, x, y in decision_points:
    # Decision box
    dec_box = FancyBboxPatch((x-8, y-2.5), 16, 5,
                             boxstyle='round,pad=0.5',
                             facecolor=color, edgecolor='black', linewidth=2)
    ax.add_patch(dec_box)
    ax.text(x, y, decision, ha='center', va='center',
            fontsize=10, fontweight='bold', color='white')

# Arrows from manager to decisions
ax.arrow(45, 54, -23, -6, head_width=1.5, head_length=1,
         fc=color_verified, ec=color_verified, linewidth=2)
ax.arrow(50, 54, 0, -6, head_width=1.5, head_length=1,
         fc=color_followup, ec=color_followup, linewidth=2)
ax.arrow(55, 54, 23, -6, head_width=1.5, head_length=1,
         fc=color_rejected, ec=color_rejected, linewidth=2)

# Section 8: Verified Path
ax.text(20, 38, 'VERIFIED PATH', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

verified_steps = [
    ('Update Prospect', 20, 33),
    ('Status: verified', 20, 29),
    ('Create/Update Lead', 20, 25),
    ('Assign Lead', 20, 21),
    ('Send Notification\n to Telecaller', 20, 17),
]

for i, (step, x, y) in enumerate(verified_steps):
    step_box = FancyBboxPatch((x-7, y-1.5), 14, 3,
                              boxstyle='round,pad=0.3',
                              facecolor=color_verified, edgecolor='black', linewidth=1.5)
    ax.add_patch(step_box)
    ax.text(x, y, step, ha='center', va='center',
            fontsize=9, fontweight='bold', color='white')
    
    if i < len(verified_steps) - 1:
        ax.arrow(x, y-1.5, 0, -2, head_width=1, head_length=0.5,
                 fc=color_verified, ec=color_verified, linewidth=1.5)

# Verified details
verified_details_box = FancyBboxPatch((2, 10), 16, 4,
                                     boxstyle='round,pad=0.3',
                                     facecolor='#E8F5E9', edgecolor=color_verified, linewidth=1.5)
ax.add_patch(verified_details_box)
verified_detail_text = 'Updates:\n• verified_at\n• verified_by\n• lead_status\n• Lead created\n• Assignment active'
ax.text(10, 12, verified_detail_text, ha='left', va='center', fontsize=8, fontweight='bold')

# Section 9: Follow-up Path
ax.text(50, 38, 'FOLLOW-UP PATH', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

followup_steps = [
    ('Update Prospect', 50, 33),
    ('Status: pending_verification\n(Remains)', 50, 29),
    ('Create Follow-up Task', 50, 25),
    ('Task Scheduled\nfor Follow-up Date', 50, 21),
]

for i, (step, x, y) in enumerate(followup_steps):
    step_box = FancyBboxPatch((x-7, y-1.5), 14, 3,
                              boxstyle='round,pad=0.3',
                              facecolor=color_followup, edgecolor='black', linewidth=1.5)
    ax.add_patch(step_box)
    ax.text(x, y, step, ha='center', va='center',
            fontsize=9, fontweight='bold', color='white')
    
    if i < len(followup_steps) - 1:
        ax.arrow(x, y-1.5, 0, -2, head_width=1, head_length=0.5,
                 fc=color_followup, ec=color_followup, linewidth=1.5)

# Follow-up details
followup_details_box = FancyBboxPatch((42, 10), 16, 4,
                                     boxstyle='round,pad=0.3',
                                     facecolor='#F3E5F5', edgecolor=color_followup, linewidth=1.5)
ax.add_patch(followup_details_box)
followup_detail_text = 'Details:\n• Verification pending\n• Follow-up date set\n• Task created\n• Will call later'
ax.text(50, 12, followup_detail_text, ha='left', va='center', fontsize=8, fontweight='bold')

# Section 10: Rejected Path
ax.text(80, 38, 'REJECTED PATH', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

rejected_steps = [
    ('Update Prospect', 80, 33),
    ('Status: rejected', 80, 29),
    ('Set Rejection Reason', 80, 25),
    ('Send Notification\n to Telecaller', 80, 21),
]

for i, (step, x, y) in enumerate(rejected_steps):
    step_box = FancyBboxPatch((x-7, y-1.5), 14, 3,
                              boxstyle='round,pad=0.3',
                              facecolor=color_rejected, edgecolor='black', linewidth=1.5)
    ax.add_patch(step_box)
    ax.text(x, y, step, ha='center', va='center',
            fontsize=9, fontweight='bold', color='white')
    
    if i < len(rejected_steps) - 1:
        ax.arrow(x, y-1.5, 0, -2, head_width=1, head_length=0.5,
                 fc=color_rejected, ec=color_rejected, linewidth=1.5)

# Rejected details
rejected_details_box = FancyBboxPatch((82, 10), 16, 4,
                                     boxstyle='round,pad=0.3',
                                     facecolor='#FFEBEE', edgecolor=color_rejected, linewidth=1.5)
ax.add_patch(rejected_details_box)
rejected_detail_text = 'Updates:\n• verification_status\n• rejection_reason\n• verified_at\n• Notification sent'
ax.text(90, 12, rejected_detail_text, ha='left', va='center', fontsize=8, fontweight='bold')

# Section 11: Status Summary
ax.text(50, 8, 'VERIFICATION STATUS SUMMARY', ha='center', va='center',
        fontsize=16, fontweight='bold', color='#2C3E50',
        bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

status_summary = [
    ('pending_verification', color_pending, 20, 4),
    ('verified', color_verified, 50, 4),
    ('rejected', color_rejected, 80, 4),
]

for status, color, x, y in status_summary:
    status_box = FancyBboxPatch((x-8, y-1.5), 16, 3,
                                boxstyle='round,pad=0.3',
                                facecolor=color, edgecolor='black', linewidth=2)
    ax.add_patch(status_box)
    ax.text(x, y, status.upper(), ha='center', va='center',
            fontsize=11, fontweight='bold', color='white')

# Footer
ax.text(50, 0.5, 'Verification Flow Diagram - Laravel CRM System',
        ha='center', va='center', fontsize=10, style='italic', color='#7F8C8D')

plt.tight_layout()
pdf.savefig(fig, bbox_inches='tight')
plt.close()

# Page 2: API Endpoints and Data Flow
fig2 = plt.figure(figsize=(20, 28))
ax2 = fig2.add_subplot(111)
ax2.set_xlim(0, 100)
ax2.set_ylim(0, 140)
ax2.axis('off')

ax2.text(50, 135, 'VERIFICATION FLOW - API ENDPOINTS & DATA STRUCTURE', 
         ha='center', va='center', fontsize=24, fontweight='bold',
         bbox=dict(boxstyle='round,pad=1', facecolor='#2C3E50', edgecolor='black', linewidth=2))

# Section: API Endpoints
ax2.text(50, 125, 'API ENDPOINTS', ha='center', va='center',
         fontsize=18, fontweight='bold', color='#2C3E50',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

endpoints = [
    ('POST /api/telecaller/interested', 'Create Prospect', 25, 118, color_telecaller),
    ('GET /api/sales-manager/tasks', 'Get Verification Tasks', 50, 118, color_manager),
    ('POST /api/sales-manager/tasks/{id}/verify', 'Verify Prospect', 25, 112, color_verified),
    ('POST /api/sales-manager/tasks/{id}/reject', 'Reject Prospect', 50, 112, color_rejected),
    ('GET /api/telecaller/verification-pending', 'View Pending Verifications', 75, 112, color_telecaller),
]

for endpoint, desc, x, y, color in endpoints:
    endpoint_box = FancyBboxPatch((x-12, y-1.5), 24, 3,
                                  boxstyle='round,pad=0.3',
                                  facecolor=color, edgecolor='black', linewidth=1.5)
    ax2.add_patch(endpoint_box)
    ax2.text(x, y, f'{endpoint}\n{desc}', ha='center', va='center',
            fontsize=9, fontweight='bold', color='white')

# Section: Prospect Model Fields
ax2.text(50, 105, 'PROSPECT MODEL - KEY FIELDS', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50')

prospect_fields = [
    ('Basic Fields', 'customer_name, phone, budget, preferred_location, size'),
    ('Status Fields', 'verification_status, verified_at, verified_by, rejection_reason'),
    ('Assignment Fields', 'telecaller_id, manager_id, assigned_manager'),
    ('Data Fields', 'lead_score, employee_remark, manager_remark, lead_status'),
    ('Relationships', 'lead_id, assignment_id, created_by'),
]

field_y = 98
for field_type, fields in prospect_fields:
    field_box = FancyBboxPatch((5, field_y-1), 90, 2,
                               boxstyle='round,pad=0.3',
                               facecolor='#E8F4F8', edgecolor='#3498DB', linewidth=1.5)
    ax2.add_patch(field_box)
    ax2.text(7, field_y, f'{field_type}:', ha='left', va='center',
            fontsize=10, fontweight='bold', color='#2C3E50')
    ax2.text(25, field_y, fields, ha='left', va='center',
            fontsize=9, color='#34495E')
    field_y -= 2.5

# Section: Events & Listeners
ax2.text(50, 82, 'EVENTS & LISTENERS', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50')

event_box2 = FancyBboxPatch((5, 74), 42, 6,
                            boxstyle='round,pad=0.3',
                            facecolor='#F3E5F5', edgecolor=color_event, linewidth=2)
ax2.add_patch(event_box2)
event_text = 'EVENT: ProspectSentForVerification\n\nFired when:\n• Prospect created with\n  verification_status = pending_verification\n• Contains prospect object'
ax2.text(26, 77, event_text, ha='center', va='center',
         fontsize=10, fontweight='bold')

listener_box2 = FancyBboxPatch((53, 74), 42, 6,
                               boxstyle='round,pad=0.3',
                               facecolor='#FFF9E6', edgecolor='#E67E22', linewidth=2)
ax2.add_patch(listener_box2)
listener_text = 'LISTENER: CreateManagerVerificationCallTask\n\nActions:\n• Creates phone_call task\n• Assigns to manager\n• Schedules +10 minutes\n• Links to lead'
ax2.text(74, 77, listener_text, ha='center', va='center',
         fontsize=10, fontweight='bold')

# Section: Verification Process Details
ax2.text(50, 65, 'VERIFICATION PROCESS DETAILS', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

# Verified Process
verified_process_box = FancyBboxPatch((5, 54), 28, 9,
                                      boxstyle='round,pad=0.3',
                                      facecolor='#E8F5E9', edgecolor=color_verified, linewidth=2)
ax2.add_patch(verified_process_box)
verified_process_text = 'VERIFIED PROCESS:\n\n1. Update prospect.verification_status = "verified"\n2. Set verified_at, verified_by\n3. Update lead fields\n4. Create/Update Lead record\n5. Create LeadAssignment\n6. Fire LeadAssigned event\n7. Send notification to telecaller\n8. Mark task as completed'
ax2.text(19, 58.5, verified_process_text, ha='left', va='center',
         fontsize=9, fontweight='bold')

# Follow-up Process
followup_process_box = FancyBboxPatch((36, 54), 28, 9,
                                      boxstyle='round,pad=0.3',
                                      facecolor='#F3E5F5', edgecolor=color_followup, linewidth=2)
ax2.add_patch(followup_process_box)
followup_process_text = 'FOLLOW-UP PROCESS:\n\n1. Keep verification_status = "pending_verification"\n2. Update prospect with follow-up info\n3. Set lead_status = "warm"\n4. Create follow-up Task\n5. Schedule task for follow-up date\n6. Mark current task as completed\n7. Prospect remains pending verification'
ax2.text(50, 58.5, followup_process_text, ha='left', va='center',
         fontsize=9, fontweight='bold')

# Rejected Process
rejected_process_box = FancyBboxPatch((67, 54), 28, 9,
                                      boxstyle='round,pad=0.3',
                                      facecolor='#FFEBEE', edgecolor=color_rejected, linewidth=2)
ax2.add_patch(rejected_process_box)
rejected_process_text = 'REJECTED PROCESS:\n\n1. Update verification_status = "rejected"\n2. Set rejection_reason\n3. Set verified_at (timestamp)\n4. Clear verified_by\n5. Update notes with reason\n6. Mark task as completed\n7. Send notification to telecaller\n8. Prospect marked as rejected'
ax2.text(81, 58.5, rejected_process_text, ha='left', va='center',
         fontsize=9, fontweight='bold')

# Section: Task Structure
ax2.text(50, 42, 'TASK STRUCTURE FOR VERIFICATION', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50')

task_structure_box = FancyBboxPatch((10, 32), 80, 8,
                                    boxstyle='round,pad=0.3',
                                    facecolor='#FFF9E6', edgecolor=color_task, linewidth=2)
ax2.add_patch(task_structure_box)
task_structure_text = """Task Model Fields:
• id, lead_id, assigned_to, created_by
• type: 'phone_call' (for verification tasks)
• title: "Verify prospect: {customer_name}"
• description: "Manager verification call task"
• status: 'pending' → 'completed'
• scheduled_at: now() + 10 minutes (initially)
• notes: Additional information

Task Status Flow:
pending → in_progress → completed (or cancelled)

Task is shown in Manager's task list with:
- Customer name and phone
- Prospect details
- Scheduled time
- Status badge"""
ax2.text(50, 36, task_structure_text, ha='center', va='center',
         fontsize=10, fontweight='bold', family='monospace')

# Section: Notification Flow
ax2.text(50, 22, 'NOTIFICATION FLOW', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

notification_steps = [
    ('Verification\nCompleted', 20, 18),
    ('Notification\nService', 40, 18),
    ('Send to\nTelecaller', 60, 18),
    ('Real-time\nUpdate', 80, 18),
]

for i, (step, x, y) in enumerate(notification_steps):
    notif_box = FancyBboxPatch((x-8, y-1.5), 16, 3,
                               boxstyle='round,pad=0.3',
                               facecolor='#9B59B6', edgecolor='black', linewidth=1.5)
    ax2.add_patch(notif_box)
    ax2.text(x, y, step, ha='center', va='center',
            fontsize=10, fontweight='bold', color='white')
    
    if i < len(notification_steps) - 1:
        ax2.arrow(x+8, y, 8, 0, head_width=1, head_length=1,
                 fc='#9B59B6', ec='#9B59B6', linewidth=2)

# Notification details
notif_details_box = FancyBboxPatch((20, 12), 60, 4,
                                   boxstyle='round,pad=0.3',
                                   facecolor='#F3E5F5', edgecolor='#9B59B6', linewidth=1.5)
ax2.add_patch(notif_details_box)
notif_detail_text = 'Notification contains: lead_id, prospect_id, verification_status, lead_status, manager_remark, verified_at, manager_name'
ax2.text(50, 14, notif_detail_text, ha='center', va='center',
         fontsize=10, fontweight='bold')

# Section: Status Transitions
ax2.text(50, 6, 'VERIFICATION STATUS TRANSITIONS', ha='center', va='center',
         fontsize=14, fontweight='bold', color='#2C3E50')

transitions_text = """
pending_verification → verified (when manager verifies with Hot/Warm/Cold/Junk)
pending_verification → rejected (when manager rejects with reason)
pending_verification → pending_verification (when Follow-up is selected - remains pending)

Note: Once verified or rejected, status cannot change back to pending_verification
"""
ax2.text(50, 2, transitions_text, ha='center', va='top',
         fontsize=10, family='monospace',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#F5F5F5', edgecolor='#CCCCCC', linewidth=1))

ax2.text(50, 0.5, 'Page 2 of 2 - API Endpoints & Data Structure',
         ha='center', va='center', fontsize=10, style='italic', color='#7F8C8D')

plt.tight_layout()
pdf.savefig(fig2, bbox_inches='tight')
plt.close()

pdf.close()
print("PDF generated successfully: Verification_Flow_Diagram.pdf")
