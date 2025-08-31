# 🔧 Database Fix Guide

## ❌ **Problem: Foreign Key Constraint Error**

You encountered this error:
```
#1452 - Cannot add or update a child row: a foreign key constraint fails
```

This happens because the database schema tries to insert data in the wrong order.

## ✅ **Solution: Follow These Steps**

### **Step 1: Drop and Recreate Database**
1. Go to phpMyAdmin
2. Select your `phels_db` database
3. Click **"Operations"** tab
4. Scroll down to **"Delete database"**
5. Click **"Drop the database"**
6. Confirm deletion

### **Step 2: Create Fresh Database**
1. Click **"New"** on the left sidebar
2. Enter database name: `phels_db`
3. Click **"Create"**

### **Step 3: Import Fixed Schema**
1. Select the `phels_db` database
2. Click **"Import"** tab
3. Click **"Choose File"**
4. Select the **updated** `database_schema.sql` file
5. Click **"Go"**

### **Step 4: Create User Accounts**
1. After successful import, go to your project folder
2. Open: `http://localhost/PHELS%20Project/create_users.php`
3. This will create the user accounts with proper passwords

## 🔐 **User Accounts Created**

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Emergency Manager |
| `fieldworker` | `field123` | Field Worker |
| `hospital` | `hospital123` | Hospital Staff |
| `pharma` | `pharma123` | Pharmaceutical |

## 🎯 **What Was Fixed**

1. **Table Creation Order**: Tables are now created in the correct order
2. **Data Insertion Order**: Parent tables are populated before child tables
3. **Foreign Key Constraints**: All relationships are properly established
4. **Password Hashing**: Users get proper password hashes

## 🚀 **Test Your System**

1. **Access**: `http://localhost/PHELS%20Project`
2. **Login** with any of the accounts above
3. **Verify** each dashboard loads correctly
4. **Check** all features work properly

## 📁 **Files You Need**

- ✅ `database_schema.sql` (updated version)
- ✅ `create_users.php` (creates user accounts)
- ✅ All other PHELS files

## 🎉 **Success!**

After following these steps, your PHELS system will be fully functional with:
- ✅ Working database
- ✅ User authentication
- ✅ Role-based dashboards
- ✅ All features operational

---

**Need Help?** Check the `README.md` file for complete setup instructions!
