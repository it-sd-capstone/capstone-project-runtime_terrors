<?php
echo "Admin: " . password_hash("Admin123@", PASSWORD_DEFAULT) . "\n";
echo "Provider: " . password_hash("Provider123@", PASSWORD_DEFAULT) . "\n";
echo "Patient: " . password_hash("Patient123@", PASSWORD_DEFAULT) . "\n";
// copy and paste the results in db fill