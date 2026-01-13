<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* CSS Reset & Style Dasar */
        body { background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        table { border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; }
        table td { font-family: sans-serif; font-size: 14px; vertical-align: top; }
        
        /* Container Putih */
        .container { display: block; margin: 0 auto !important; max-width: 580px; padding: 10px; width: 580px; }
        .content { box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px; }
        .main { background: #ffffff; border-radius: 8px; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .wrapper { box-sizing: border-box; padding: 30px; }
        
        /* Typography */
        h1 { color: #111827; font-size: 20px; font-weight: 700; margin: 0 0 15px; text-align: center; }
        p { color: #4b5563; font-size: 15px; font-weight: normal; margin: 0 0 20px; line-height: 1.6; }
        
        /* Button Hijau WFIED */
        .btn { box-sizing: border-box; width: 100%; }
        .btn > tbody > tr > td { padding-bottom: 15px; }
        .btn table { width: auto; }
        .btn table td { background-color: #ffffff; border-radius: 5px; text-align: center; }
        .btn a { background-color: #16a34a; /* WARNA HIJAU */ border: solid 1px #16a34a; border-radius: 6px; box-sizing: border-box; color: #ffffff; cursor: pointer; display: inline-block; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-decoration: none; text-transform: capitalize; }
        
        /* Footer */
        .footer { clear: both; margin-top: 10px; text-align: center; width: 100%; }
        .footer td, .footer p, .footer span, .footer a { color: #9ca3af; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">

                    <div style="text-align: center; margin-bottom: 20px;">
                        <span style="font-size: 24px; font-weight: 800; color: #16a34a; letter-spacing: -1px;">WFIED MEMBERSHIP</span>
                    </div>

                    <table role="presentation" class="main">
                        <tr>
                            <td class="wrapper">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <h1>Verify Your Email Address</h1>
                                            <p>Hi <strong>{{ $user->name }}</strong>,</p>
                                            <p>Welcome to WFIED Membership! We're excited to have you on board. To get started, please verify your email address by clicking the button below.</p>
                                            
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td> <a href="{{ $url }}" target="_blank">Verify Account</a> </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <p>This link will expire in 60 minutes. If you didn't create an account, you can safely ignore this email.</p>
                                            <p>Best regards,<br>The WFIED Team</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block">
                                    <span class="apple-link">WFIED Membership System, Indonesia</span>
                                    <br> Don't like these emails? <a href="#">Unsubscribe</a>.
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>