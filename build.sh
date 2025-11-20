#!/bin/bash

echo "🚀 Starting build process for Billing Rental System..."

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p uploads
mkdir -p uploads/rooms
mkdir -p uploads/products
mkdir -p uploads/profiles
mkdir -p img

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 uploads
chmod -R 755 img

# Create .htaccess if not exists (for Apache compatibility)
if [ ! -f ".htaccess" ]; then
    echo "📝 Creating .htaccess..."
    cat > .htaccess << 'EOF'
# Enable Rewrite Engine
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Remove trailing slash
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/$ /$1 [L,R=301]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory browsing
Options -Indexes

# PHP Settings
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>
EOF
fi

echo "✅ Build completed successfully!"
echo "📦 Application is ready to deploy"
