#!/usr/bin/env python3
"""
Generate a comprehensive Lead Flow Diagram in PDF format
for Laravel CRM System
"""

from matplotlib import pyplot as plt
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch, Circle, Rectangle
import matplotlib.patches as mpatches
from matplotlib.backends.backend_pdf import PdfPages
import numpy as np

# Create PDF
pdf = PdfPages('Lead_Flow_Diagram_Complete.pdf')

# Create figure with larger size for better readability
fig = plt.figure(figsize=(20, 28))
ax = fig.add_subplot(111)
ax.set_xlim(0, 100)
ax.set_ylim(0, 140)
ax.axis('off')

# Colors
color_new = '#4A90E2'  # Blue
color_contacted = '#7B68EE'  # Medium Slate Blue
color_qualified = '#32CD32'  # Lime Green
color_site_visit = '#FF8C00'  # Dark Orange
color_negotiation = '#FFD700'  # Gold
color_closed_won = '#228B22'  # Forest Green
color_closed_lost = '#DC143C'  # Crimson
color_on_hold = '#FFA500'  # Orange
color_special = '#9370DB'  # Medium Purple

# Title
ax.text(50, 135, 'LARAVEL CRM - COMPLETE LEAD FLOW DIAGRAM', 
        ha='center', va='center', fontsize=24, fontweight='bold',
        bbox=dict(boxstyle='round,pad=1', facecolor='#2C3E50', edgecolor='black', linewidth=2))

# Section 1: Lead Sources (Top)
ax.text(50, 128, 'LEAD SOURCES', ha='center', va='center', 
        fontsize=18, fontweight='bold', color='#2C3E50')

sources = ['Website', 'Referral', 'Walk-in', 'Call', 'Social Media', 'Google Sheets', 'Other']
source_x = 10
source_y = 123
source_width = 12
source_height = 3
source_spacing = 11.5

for i, source in enumerate(sources):
    x = source_x + (i % 4) * source_spacing
    y = source_y - (i // 4) * 4
    rect = FancyBboxPatch((x, y), source_width, source_height,
                         boxstyle='round,pad=0.3', 
                         facecolor='#E8F4F8', edgecolor='#3498DB', linewidth=1.5)
    ax.add_patch(rect)
    ax.text(x + source_width/2, y + source_height/2, source, 
            ha='center', va='center', fontsize=10, fontweight='bold')

# Arrow from sources to Lead Creation
ax.arrow(50, 118, 0, -3, head_width=2, head_length=1, 
         fc='#34495E', ec='#34495E', linewidth=2)

# Section 2: Lead Creation & Initial Status
ax.text(50, 112, 'LEAD CREATION', ha='center', va='center',
        fontsize=16, fontweight='bold', color='#2C3E50')

lead_creation_box = FancyBboxPatch((35, 108), 30, 4,
                                   boxstyle='round,pad=0.5',
                                   facecolor=color_new, edgecolor='black', linewidth=2)
ax.add_patch(lead_creation_box)
ax.text(50, 110, 'NEW LEAD\n(Status: NEW)', ha='center', va='center',
        fontsize=12, fontweight='bold', color='white')

# Section 3: Lead Assignment
ax.text(50, 101, 'LEAD ASSIGNMENT METHODS', ha='center', va='center',
        fontsize=16, fontweight='bold', color='#2C3E50')

assignment_methods = ['Manual\nAssignment', 'Auto Round\nRobin', 'Percentage\nBased', 
                     'Fixed Count', 'Linked\nTelecaller', 'Smart Import']
assign_x = 8
assign_y = 96
assign_width = 13
assign_height = 3.5
assign_spacing = 14

for i, method in enumerate(assignment_methods):
    x = assign_x + (i % 3) * assign_spacing
    y = assign_y - (i // 3) * 4.5
    rect = FancyBboxPatch((x, y), assign_width, assign_height,
                         boxstyle='round,pad=0.3',
                         facecolor='#F0F8FF', edgecolor='#4682B4', linewidth=1.5)
    ax.add_patch(rect)
    ax.text(x + assign_width/2, y + assign_height/2, method,
            ha='center', va='center', fontsize=9, fontweight='bold')

# Arrow to assignment roles
ax.arrow(50, 90, 0, -2, head_width=2, head_length=1,
         fc='#34495E', ec='#34495E', linewidth=2)

# Assignment Roles
ax.text(50, 85, 'ASSIGNED TO', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

roles = ['Telecaller', 'Sales Executive', 'Sales Manager']
role_x = 30
role_y = 80
role_width = 12
role_height = 3
role_spacing = 16

for i, role in enumerate(roles):
    x = role_x + i * role_spacing
    rect = FancyBboxPatch((x, role_y), role_width, role_height,
                         boxstyle='round,pad=0.3',
                         facecolor='#FFF8DC', edgecolor='#DAA520', linewidth=1.5)
    ax.add_patch(rect)
    ax.text(x + role_width/2, role_y + role_height/2, role,
            ha='center', va='center', fontsize=10, fontweight='bold')

# Main Flow Arrow
ax.arrow(50, 76, 0, -3, head_width=2.5, head_length=1.5,
         fc='#E74C3C', ec='#E74C3C', linewidth=3)

# Section 4: Main Lead Status Flow
ax.text(50, 70, 'LEAD STATUS FLOW', ha='center', va='center',
        fontsize=18, fontweight='bold', color='#2C3E50', 
        bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

# Status boxes with colors
statuses = [
    ('NEW', color_new, 50, 65),
    ('CONTACTED', color_contacted, 50, 58),
    ('QUALIFIED', color_qualified, 50, 51),
    ('SITE VISIT\nSCHEDULED', color_site_visit, 50, 44),
    ('SITE VISIT\nCOMPLETED', color_site_visit, 50, 37),
    ('NEGOTIATION', color_negotiation, 50, 30),
]

# Draw status boxes
for status_name, color, x, y in statuses:
    box = FancyBboxPatch((x-8, y-1.5), 16, 3,
                         boxstyle='round,pad=0.5',
                         facecolor=color, edgecolor='black', linewidth=2)
    ax.add_patch(box)
    ax.text(x, y, status_name, ha='center', va='center',
            fontsize=11, fontweight='bold', color='white')

# Arrows between statuses
for i in range(len(statuses)-1):
    y_start = statuses[i][2] - 1.5
    y_end = statuses[i+1][2] + 1.5
    ax.arrow(50, y_start, 0, y_end - y_start, 
             head_width=2, head_length=1,
             fc='#2C3E50', ec='#2C3E50', linewidth=2)

# Final Statuses (Side by side)
final_statuses = [
    ('CLOSED WON', color_closed_won, 30, 23),
    ('CLOSED LOST', color_closed_lost, 50, 23),
    ('ON HOLD', color_on_hold, 70, 23),
]

for status_name, color, x, y in final_statuses:
    box = FancyBboxPatch((x-8, y-1.5), 16, 3,
                         boxstyle='round,pad=0.5',
                         facecolor=color, edgecolor='black', linewidth=2)
    ax.add_patch(box)
    ax.text(x, y, status_name, ha='center', va='center',
            fontsize=11, fontweight='bold', color='white')

# Arrows to final statuses from Negotiation
ax.arrow(42, 28.5, -10, -4, head_width=2, head_length=1,
         fc='#228B22', ec='#228B22', linewidth=2)
ax.arrow(50, 28.5, 0, -4, head_width=2, head_length=1,
         fc='#DC143C', ec='#DC143C', linewidth=2)
ax.arrow(58, 28.5, 10, -4, head_width=2, head_length=1,
         fc='#FFA500', ec='#FFA500', linewidth=2)

# Section 5: Side Processes
# Left side - Site Visits
ax.text(15, 50, 'SITE VISIT PROCESS', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

site_visit_stages = [
    ('Schedule', 15, 45),
    ('In Progress', 15, 40),
    ('Completed', 15, 35),
    ('Cancelled', 15, 30),
    ('Rescheduled', 15, 25),
]

for stage, x, y in site_visit_stages:
    box = FancyBboxPatch((x-5, y-1), 10, 2,
                         boxstyle='round,pad=0.3',
                         facecolor='#FFE4B5', edgecolor='#FF8C00', linewidth=1.5)
    ax.add_patch(box)
    ax.text(x, y, stage, ha='center', va='center',
            fontsize=9, fontweight='bold')

# Arrows for site visit flow
for i in range(len(site_visit_stages)-1):
    y_start = site_visit_stages[i][1] - 1
    y_end = site_visit_stages[i+1][1] + 1
    ax.arrow(15, y_start, 0, y_end - y_start,
             head_width=1, head_length=0.5,
             fc='#FF8C00', ec='#FF8C00', linewidth=1.5)

# Connection from Site Visit Scheduled to Site Visit Process
ax.arrow(42, 44, -25, 0, head_width=1, head_length=1,
         fc='#FF8C00', ec='#FF8C00', linewidth=2, linestyle='--')

# Right side - Follow-ups
ax.text(85, 50, 'FOLLOW-UP PROCESS', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

followup_stages = [
    ('Scheduled', 85, 45),
    ('Completed', 85, 40),
    ('Missed', 85, 35),
    ('Cancelled', 85, 30),
]

for stage, x, y in followup_stages:
    box = FancyBboxPatch((x-5, y-1), 10, 2,
                         boxstyle='round,pad=0.3',
                         facecolor='#E6E6FA', edgecolor='#9370DB', linewidth=1.5)
    ax.add_patch(box)
    ax.text(x, y, stage, ha='center', va='center',
            fontsize=9, fontweight='bold')

# Arrows for follow-up flow
for i in range(len(followup_stages)-1):
    y_start = followup_stages[i][1] - 1
    y_end = followup_stages[i+1][1] + 1
    ax.arrow(85, y_start, 0, y_end - y_start,
             head_width=1, head_length=0.5,
             fc='#9370DB', ec='#9370DB', linewidth=1.5)

# Section 6: Special States
ax.text(50, 18, 'SPECIAL STATES & ACTIONS', ha='center', va='center',
        fontsize=16, fontweight='bold', color='#2C3E50',
        bbox=dict(boxstyle='round,pad=0.5', facecolor='#FFFACD', edgecolor='#DAA520', linewidth=2))

special_states = [
    ('DEAD LEAD', '#8B0000', 20, 13),
    ('BLOCKED LEAD', '#8B4513', 40, 13),
    ('VERIFICATION\nPENDING', '#FF6347', 60, 13),
    ('VERIFIED', '#32CD32', 80, 13),
]

for state, color, x, y in special_states:
    box = FancyBboxPatch((x-7, y-1.5), 14, 3,
                         boxstyle='round,pad=0.3',
                         facecolor=color, edgecolor='black', linewidth=2)
    ax.add_patch(box)
    ax.text(x, y, state, ha='center', va='center',
            fontsize=10, fontweight='bold', color='white')

# Section 7: Status Transition Possibilities
ax.text(50, 8, 'STATUS TRANSITION POSSIBILITIES', ha='center', va='center',
        fontsize=14, fontweight='bold', color='#2C3E50')

# Create a detailed transition table
transitions_text = """
NEW → CONTACTED (When telecaller makes first contact)
CONTACTED → QUALIFIED (When lead shows interest and meets criteria)
CONTACTED → CLOSED LOST (If not interested)
QUALIFIED → SITE VISIT SCHEDULED (When site visit is booked)
SITE VISIT SCHEDULED → SITE VISIT COMPLETED (After visit)
SITE VISIT COMPLETED → NEGOTIATION (If interested after visit)
SITE VISIT COMPLETED → CLOSED LOST (If not interested)
NEGOTIATION → CLOSED WON (Deal finalized)
NEGOTIATION → CLOSED LOST (Deal failed)
NEGOTIATION → ON HOLD (Temporarily paused)
ANY STATUS → ON HOLD (Can be put on hold from any stage)
ANY STATUS → CLOSED LOST (Can be marked as lost at any stage)
ON HOLD → Any previous status (Can resume from hold)
"""

ax.text(50, 2, transitions_text, ha='center', va='top',
        fontsize=9, family='monospace',
        bbox=dict(boxstyle='round,pad=0.5', facecolor='#F5F5F5', edgecolor='#CCCCCC', linewidth=1))

# Legend
legend_elements = [
    mpatches.Patch(facecolor=color_new, label='NEW', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_contacted, label='CONTACTED', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_qualified, label='QUALIFIED', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_site_visit, label='SITE VISIT', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_negotiation, label='NEGOTIATION', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_closed_won, label='CLOSED WON', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_closed_lost, label='CLOSED LOST', edgecolor='black', linewidth=1.5),
    mpatches.Patch(facecolor=color_on_hold, label='ON HOLD', edgecolor='black', linewidth=1.5),
]

ax.legend(handles=legend_elements, loc='upper right', bbox_to_anchor=(0.98, 0.98),
          fontsize=10, framealpha=0.9, title='STATUS COLORS', title_fontsize=11)

# Footer
ax.text(50, 0.5, 'Generated for Laravel CRM System - All Lead Flow Possibilities',
        ha='center', va='center', fontsize=10, style='italic', color='#7F8C8D')

plt.tight_layout()
pdf.savefig(fig, bbox_inches='tight')
plt.close()

# Page 2: Detailed Role-Based Flow
fig2 = plt.figure(figsize=(20, 28))
ax2 = fig2.add_subplot(111)
ax2.set_xlim(0, 100)
ax2.set_ylim(0, 140)
ax2.axis('off')

ax2.text(50, 135, 'ROLE-BASED LEAD FLOW & PERMISSIONS', 
         ha='center', va='center', fontsize=24, fontweight='bold',
         bbox=dict(boxstyle='round,pad=1', facecolor='#2C3E50', edgecolor='black', linewidth=2))

# Roles section
roles_detail = [
    {
        'name': 'ADMIN',
        'y': 125,
        'color': '#E74C3C',
        'permissions': [
            'View all leads',
            'Create/Edit/Delete leads',
            'Assign leads to anyone',
            'View all reports',
            'Manage users and roles',
            'Access all features'
        ]
    },
    {
        'name': 'CRM',
        'y': 110,
        'color': '#3498DB',
        'permissions': [
            'View all leads',
            'Create/Edit leads',
            'Assign leads',
            'Import from Google Sheets',
            'Manage smart imports',
            'View verification requests'
        ]
    },
    {
        'name': 'SALES HEAD',
        'y': 95,
        'color': '#9B59B6',
        'permissions': [
            'View verified prospects only',
            'View verified site visits',
            'View closed leads (won/lost)',
            'View team performance',
            'Cannot edit leads directly'
        ]
    },
    {
        'name': 'SALES MANAGER',
        'y': 80,
        'color': '#F39C12',
        'permissions': [
            'View team leads',
            'Assign leads to team',
            'Verify prospects',
            'Verify site visits',
            'View team reports',
            'Transfer leads'
        ]
    },
    {
        'name': 'SALES EXECUTIVE',
        'y': 65,
        'color': '#1ABC9C',
        'permissions': [
            'View assigned leads',
            'Update lead status',
            'Schedule site visits',
            'Complete site visits',
            'Create follow-ups',
            'Request verification'
        ]
    },
    {
        'name': 'TELECALLER',
        'y': 50,
        'color': '#16A085',
        'permissions': [
            'View assigned leads',
            'Contact leads',
            'Update to CONTACTED',
            'Update to QUALIFIED',
            'Create tasks',
            'Mark CNP (Called Not Picked)',
            'Request verification for prospects'
        ]
    }
]

for role_info in roles_detail:
    # Role box
    role_box = FancyBboxPatch((5, role_info['y']-2.5), 25, 5,
                              boxstyle='round,pad=0.5',
                              facecolor=role_info['color'], edgecolor='black', linewidth=2)
    ax2.add_patch(role_box)
    ax2.text(17.5, role_info['y'], role_info['name'],
            ha='center', va='center', fontsize=14, fontweight='bold', color='white')
    
    # Permissions
    perm_x = 35
    perm_y = role_info['y']
    perm_text = '\n'.join([f'• {p}' for p in role_info['permissions']])
    ax2.text(perm_x, perm_y, perm_text,
            ha='left', va='center', fontsize=9,
            bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#BDC3C7', linewidth=1))

# Section: Lead Assignment Flow
ax2.text(50, 40, 'LEAD ASSIGNMENT FLOW', ha='center', va='center',
         fontsize=18, fontweight='bold', color='#2C3E50',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

assignment_flow = [
    ('Lead Created', 20, 35),
    ('Auto Assignment\nRules Check', 40, 35),
    ('Assigned to\nUser', 60, 35),
    ('Notification\nSent', 80, 35),
]

for step, x, y in assignment_flow:
    box = FancyBboxPatch((x-8, y-1.5), 16, 3,
                         boxstyle='round,pad=0.3',
                         facecolor='#D5E8D4', edgecolor='#82B366', linewidth=1.5)
    ax2.add_patch(box)
    ax2.text(x, y, step, ha='center', va='center',
            fontsize=10, fontweight='bold')

# Arrows
for i in range(len(assignment_flow)-1):
    x_start = assignment_flow[i][1] + 8
    x_end = assignment_flow[i+1][1] - 8
    ax2.arrow(x_start, 35, x_end - x_start, 0,
              head_width=1, head_length=1,
              fc='#82B366', ec='#82B366', linewidth=2)

# Section: Verification Process
ax2.text(50, 25, 'VERIFICATION PROCESS', ha='center', va='center',
         fontsize=18, fontweight='bold', color='#2C3E50',
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#ECF0F1', edgecolor='#34495E', linewidth=2))

verification_steps = [
    ('Request\nVerification', 15, 20),
    ('Manager\nReviews', 40, 20),
    ('Approved/\nRejected', 65, 20),
    ('Status\nUpdated', 85, 20),
]

for step, x, y in verification_steps:
    box = FancyBboxPatch((x-6, y-1.5), 12, 3,
                         boxstyle='round,pad=0.3',
                         facecolor='#FFF2CC', edgecolor='#D6B656', linewidth=1.5)
    ax2.add_patch(box)
    ax2.text(x, y, step, ha='center', va='center',
            fontsize=9, fontweight='bold')

# Arrows
for i in range(len(verification_steps)-1):
    x_start = verification_steps[i][1] + 6
    x_end = verification_steps[i+1][1] - 6
    ax2.arrow(x_start, 20, x_end - x_start, 0,
              head_width=1, head_length=1,
              fc='#D6B656', ec='#D6B656', linewidth=2)

# Section: Special Features
ax2.text(50, 12, 'SPECIAL FEATURES & INTEGRATIONS', ha='center', va='center',
         fontsize=16, fontweight='bold', color='#2C3E50')

features = [
    'Google Sheets Import & Sync',
    'Smart Import Automation',
    'SLA Tracking',
    'CNP (Called Not Picked) Tracking',
    'Lead Transfer Between Users',
    'Dead Lead Management',
    'Blocked Lead Management',
    'Real-time Notifications (Pusher)',
    'Activity Logging',
    'Task Management for Telecallers'
]

feature_text = '\n'.join([f'✓ {f}' for f in features])
ax2.text(50, 4, feature_text, ha='center', va='top',
         fontsize=10,
         bbox=dict(boxstyle='round,pad=0.5', facecolor='#E8F5E9', edgecolor='#4CAF50', linewidth=1.5))

ax2.text(50, 0.5, 'Page 2 of 2 - Role-Based Flow & Features',
         ha='center', va='center', fontsize=10, style='italic', color='#7F8C8D')

plt.tight_layout()
pdf.savefig(fig2, bbox_inches='tight')
plt.close()

pdf.close()
print("PDF generated successfully: Lead_Flow_Diagram_Complete.pdf")

