# Salameh Cargo - Shipment Tracking Platform

## Setup Instructions

1. Import database/schema.sql into your MySQL database
2. Configure database connection in includes/db.php
3. Default admin: username=admin, password=change_me

## Twilio WhatsApp Setup

To enable WhatsApp OTP functionality:

1. Create a Twilio account at [twilio.com](https://www.twilio.com)
2. Get your Account SID and Auth Token from the Twilio Console
3. Join the Twilio WhatsApp Sandbox or obtain a WhatsApp Business number
4. Create a .env file in the project root with these variables:

   ```env
   TWILIO_ACCOUNT_SID=your_account_sid
   TWILIO_AUTH_TOKEN=your_auth_token
   TWILIO_WHATSAPP_NUMBER=your_whatsapp_number
   ```

5. Update your .htaccess to load environment variables:

   ```apache
   SetEnv TWILIO_ACCOUNT_SID your_account_sid
   SetEnv TWILIO_AUTH_TOKEN your_auth_token
   SetEnv TWILIO_WHATSAPP_NUMBER your_whatsapp_number
   ```

## WhatsApp Template Message

If using a WhatsApp Business Profile, get approval for this message template:

   ```text
   Your Salameh Cargo verification code is: {{1}}

   This code will expire in 15 minutes.
   ```

For sandbox testing, users must first join your sandbox by sending a WhatsApp message with the join code to your sandbox number.

## Automated Tracking

The system includes automated tracking from multiple sources:

1. TrackTrace.ph
2. Port of Beirut
3. CMA CGM
4. MSC
5. Maersk
6. Evergreen
7. ONE

The cron job at `cron/update_shipments.php` should be scheduled to run every 12 hours:

```bash
0 */12 * * * php /path/to/project/cron/update_shipments.php
```

This will automatically update shipment statuses from the first available tracking source.
