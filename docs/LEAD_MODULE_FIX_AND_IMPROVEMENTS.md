# Lead Module Audit & Improvement Plan

## 1. Flow Architecture
Below is the visualized flow of how leads are captured, processed, and displayed in the system.

```mermaid
graph TD
    %% Entry Points
    User((Customer on FB/IG)) -->|Sends Message| Meta[Meta Graph API]
    Admin((Admin/Agent)) -->|Manual Entry| Inbox[Lead Inbox UI]

    subgraph Webhook Processing [Automated Webhook Flow]
        Meta -->|POST Request| WebhookController[MetaWebhookController]
        WebhookController -->|1. Verify| Signature{Signature Check}
        Signature -->|Valid| Parser[MetaApiService: parsePayload]
        Parser -->|Extract| EventData[SenderID, RecipientID, Message]
    end

    subgraph Backend Core [Business Logic Layer]
        EventData -->|Match platform_user_id| LeadModel{Lead Exists?}
        LeadModel -->|No| CreateLead[Create New Lead]
        LeadModel -->|Yes| UpdateLead[Update Lead Metadata]
        
        CreateLead --> ProfileFetch[Async: Fetch User Profile Name/Pic]
        ProfileFetch --> ScoreLead[LeadScoringService: Calculate Score]
        UpdateLead --> ScoreLead
        
        ScoreLead -->|Heat Logic| HeatLevel[Determine Heat: Cold/Warm/Hot]
        HeatLevel --> Assign{Auto-Assign?}
        
        Assign -->|Yes| RoundRobin[LeadAssignmentService: Round Robin]
        RoundRobin --> ActivityLog[LeadActivity: Log Event]
    end

    subgraph Database Storage [Persistence Layer]
        ActivityLog --> DB_Lead[(Table: leads)]
        ActivityLog --> DB_Conv[(Table: meta_conversations)]
        ActivityLog --> DB_Msg[(Table: meta_messages)]
    end

    subgraph Real-time Delivery [Frontend Notification]
        DB_Msg -->|Trigger Event| Broadcast[Event: NewLeadMessage]
        Broadcast -->|Pusher/WebSocket| Browser[Admin Browser]
    end

    subgraph User Interface [Lead Inbox View]
        Browser -->|Update UI| Kanban[Kanban Board]
        Inbox -->|POST| CreateLead
        
        Kanban -->|Columns| NewCol[New]
        Kanban -->|Columns| InProcessCol[In Process]
        Kanban -->|Columns| DoneCol[Completed]
        
        subgraph UI_Indicators [Visual Priority]
            NewCol --> Heat_Icons[❄️ Cold / ⚡ Warm / 🔥 Hot]
            NewCol --> SLA_Label[SLA Overdue Warning]
        end
    end

    %% Styling
    classDef primary fill:#6366f1,stroke:#4338ca,color:#fff
    classDef secondary fill:#10b981,stroke:#059669,color:#fff
    classDef danger fill:#ef4444,stroke:#dc2626,color:#fff
    classDef storage fill:#f8fafc,stroke:#64748b,color:#0f172a
    
    class WebhookController,Parser,ScoreLead primary
    class NewCol,InProcessCol,DoneCol secondary
    class SLA_Label danger
    class DB_Lead,DB_Conv,DB_Msg storage
```

---

## 2. Root Cause Analysis (Status: FIXED ✅)

The following critical issues were identified and have been resolved:

### A. Missing Configuration (Empty Meta Accounts)
The system had zero records in the `meta_accounts` table. 
- **Fix**: Added `meta_leads.settings` permission and a Setup Wizard in the UI to facilitate account connection.

### B. Inconsistent Conversation Identification
Mismatch in ID generation between Webhook and Admin Controller.
- **Fix**: Both now use a deterministic `{sender_id}_{recipient_id}` format. No more duplicate threads.

### C. Global Read Receipt Bug
Logic error marked all outgoing messages as "Read" globally.
- **Fix**: Added proper filtering by `conversation_id` in `handleReadEvent`.

### D. Redundant Scoring Logic
Duplicated logic in `Lead` model and `LeadScoringService`.
- **Fix**: Centralized all logic in `LeadScoringService`. The model now delegates to the service.

---

## 3. Applied Improvements (Real-Life User Friendly)

### 3.1 Intelligent Lead "Heat" Indicators
Leads now display visual icons in the Kanban board based on their engagement score:
- 🔥 **Hot (80+)**: Very active, needs immediate attention.
- ⚡ **Warm (40-79)**: Showing interest, needs follow-up.
- ❄️ **Cold (<40)**: Minimal engagement or old contact.

### 3.2 Automated Response Time Tracking
The system now calculates the actual average time your agents take to reply to incoming messages, giving you real performance data in the Analytics tab.

### 3.3 Quick Reply "Starter Pack"
Seeded default templates for common scenarios:
- *Greeting & Intro*
- *Price Inquiry Response*
- *Showroom Appointment Booking*
- *Customization Follow-up*

### 3.4 SLA & Priority Alerts
Visual "Overdue" badges now appear on cards that have exceeded their 24h SLA response window.
