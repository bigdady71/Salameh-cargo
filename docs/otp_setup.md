# OTP Setup Guide

## WhatsApp OTP via Twilio Messaging

Salameh Cargo uses WhatsApp for sending one-time verification codes to users during login. This guide explains how to set up and configure the WhatsApp OTP system.

### Prerequisites

1. A Twilio account (sign up at [twilio.com](https://www.twilio.com))
2. Either:
   - WhatsApp Business Profile approved by Meta (for production)
   - OR Twilio Sandbox for WhatsApp (for development/testing)

### Environment Configuration

Copy the following variables from `.env.example` to your `.env` file and update them with your Twilio credentials:

```bash
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
TWILIO_WHATSAPP_TEMPLATE_SID=your_template_sid
```

### WhatsApp Business API Setup

#### Development/Sandbox Mode

1. Go to Twilio Console > Messaging > Try it Out > Send a WhatsApp Message
2. Follow the instructions to join your sandbox
3. Users must send the provided code (e.g., "join ABC-123") to your sandbox number
4. The sandbox allows sending messages to verified numbers without templates

#### Production Mode

1. Apply for WhatsApp Business API through Twilio
2. Once approved, you'll get a WhatsApp Business number
3. Update `TWILIO_WHATSAPP_FROM` with your business number
4. Create and get approval for message templates

### Message Template Setup

1. Go to Twilio Console > Messaging > Content Editor > Create Content
2. Choose "WhatsApp Template"
3. Create a template with the following content:

   ```text
   Your verification code id: {{1}} for Salameh Cargo. Please don't share it with anyone.
   ```

4. Select category: "OTP"
5. Submit for approval
6. Once approved, copy the Content SID to `TWILIO_WHATSAPP_TEMPLATE_SID`

### Testing the Setup

1. Configure your environment variables
2. Try logging in with a WhatsApp-enabled phone number
3. Check Twilio Console > Monitor > Logs for delivery status
4. For sandbox: ensure the user has joined your sandbox first

### Troubleshooting

Common issues and solutions:

1. Message not delivered:
   - Check if user joined sandbox (dev mode)
   - Verify phone number format (E.164 format required)
   - Check Twilio logs for error messages

2. Template not working:
   - Verify template is approved
   - Check Content SID is correct
   - Ensure variable format matches template

3. Configuration issues:
   - Verify all environment variables are set
   - Check credentials are valid
   - Ensure WhatsApp Business API is active

For any issues, check the application logs at `/admin` and Twilio Console logs for detailed error messages.
