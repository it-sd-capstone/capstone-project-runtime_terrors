<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 15px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; 
                 text-decoration: none; border-radius: 5px; margin: 15px 0; }
        .footer { font-size: 12px; color: #888; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Email Verification</h2>
        </div>
        <div class="content">
            <p>Hello {{name}},</p>
            <p>Thank you for registering with our Patient Appointment System. Please verify your email address by clicking the button below:</p>
            
            <p style="text-align: center;">
                <a href="{{verification_link}}" class="button">Verify Email Address</a>
            </p>
            
            <p>If the button above doesn't work, copy and paste this link into your browser:</p>
            <p>{{verification_link}}</p>
            
            <p>This link will expire in 24 hours.</p>
            
            <p>If you did not register for an account, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>© Patient Appointment System</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>