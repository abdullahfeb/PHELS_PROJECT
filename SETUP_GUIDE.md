# 🚀 PHELS Payment System Setup Guide

## ⚠️ **IMPORTANT: Run these scripts in order!**

The payment system requires proper database setup before it can work. Follow these steps exactly:

## 📋 **Step 1: Run Payment System Setup**
1. **Open your browser** and go to: `http://localhost/PHELS%20Project/add_payment_system.php`
2. **Wait for completion** - You should see "✅ Payment system tables added successfully!"
3. **Don't close this page yet!**

## 🏥 **Step 2: Add Sample Products**
1. **In the same browser tab**, go to: `http://localhost/PHELS%20Project/add_sample_products.php`
2. **Wait for completion** - You should see "✅ Sample products added successfully!"
3. **Now you can close this page**

## 🧪 **Step 3: Test the System**
1. **Login as Hospital**: `hospital` / `hospital123`
   - Go to Dashboard → Click "Order Products"
   - You should now see available products to order

2. **Login as Pharma**: `pharma` / `pharma123`
   - Go to Dashboard → Click "Manage Orders"
   - You should see the orders dashboard

## 🔧 **What Each Script Does**

### `add_payment_system.php`
- Creates payment-related database tables
- Sets up bank accounts and mobile payment accounts
- Establishes order and invoice structures

### `add_sample_products.php`
- Adds sample medical products with realistic prices
- Links products to pharmaceutical companies
- Sets up inventory for testing

## ❌ **Common Issues & Solutions**

### **"Column not found: mi.pharma_id"**
- **Solution**: Run `add_payment_system.php` first
- This script adds the missing `pharma_id` column

### **"No products available"**
- **Solution**: Run `add_sample_products.php` after the first script
- This adds sample products to the database

### **"Tables don't exist"**
- **Solution**: Make sure you ran `fix_database.php` first (the original database setup)
- Then run the payment system scripts

## ✅ **Verification Checklist**

After running both scripts, you should have:

- [ ] **Payment Tables**: `orders`, `order_items`, `payments`, `invoices`
- [ ] **Account Tables**: `bank_accounts`, `mobile_payment_accounts`
- [ ] **Sample Products**: 9+ medical products with prices
- [ ] **Sample Accounts**: Bank and mobile payment accounts for hospitals

## 🆘 **Need Help?**

If you still encounter issues:

1. **Check XAMPP**: Make sure MySQL service is running
2. **Database Connection**: Verify your database credentials in `config.php`
3. **Error Messages**: Look for specific error messages in the setup scripts
4. **Browser Console**: Check for JavaScript errors

## 🎯 **Next Steps**

Once setup is complete:

1. **Test Hospital Ordering**: Place test orders as a hospital user
2. **Test Pharma Management**: Process orders as a pharmaceutical company
3. **Explore Features**: Try different payment methods and order statuses
4. **Customize**: Modify products, prices, and payment options as needed

---

**🎉 Congratulations!** Your PHELS Payment System is now ready to revolutionize healthcare supply chain management!
