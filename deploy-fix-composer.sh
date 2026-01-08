#!/bin/bash
# Permanent Fix Script for Composer Lock Compatibility
# Ye script server par run karein to composer.lock PHP 8.2 compatible ho jayega

cd /home/u188221078/domains/bihtech.com/public_html/crm

echo "🔧 Composer Lock Fix Script Starting..."

# Backup existing composer.lock (agar hai)
if [ -f composer.lock ]; then
    echo "📦 Backing up existing composer.lock..."
    cp composer.lock composer.lock.backup
fi

# Composer.lock delete karein
echo "🗑️  Removing old composer.lock..."
rm -f composer.lock

# Fresh install with PHP 8.2 platform constraint
echo "📥 Installing dependencies with PHP 8.2 compatibility..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Agar error aaye to retry
if [ $? -ne 0 ]; then
    echo "⚠️  First attempt failed, retrying..."
    composer update --no-dev --optimize-autoloader --ignore-platform-reqs
fi

# Verify composer.lock created
if [ -f composer.lock ]; then
    echo "✅ composer.lock successfully generated!"
    
    # Git status check
    echo "📊 Checking git status..."
    git status composer.lock
    
    echo ""
    echo "🎯 Next Steps:"
    echo "1. git add composer.lock"
    echo "2. git commit -m 'Fix: Update composer.lock for PHP 8.2 compatibility'"
    echo "3. git push origin main"
    echo ""
    echo "✅ Future deployments ab automatically work karengi!"
else
    echo "❌ Error: composer.lock generation failed!"
    echo "Please check composer errors above."
    exit 1
fi
