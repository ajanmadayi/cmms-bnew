# Render Deployment Guide for CMMS-B

This folder is prepared for Render as a PHP Docker web service with a separate MySQL private service.

## 1. Upload code to GitHub

Create a new GitHub repository and upload the contents of this `CMMS-B` folder.

## 2. Create MySQL on Render

Create a new Render MySQL private service. Do not modify any existing Render service.

Recommended values:

- Service type: Private Service
- Language: Docker
- Database name: sap_ptw
- Disk mount path: /var/lib/mysql
- Disk size: 10 GB or more

After deployment, Render will show an internal database host similar to:

```text
mysql-your-service:3306
```

## 3. Import your database

Export your local XAMPP database `sap_ptw` as an SQL file, then import it into the new Render MySQL service.

Example import command from a shell that can reach the Render MySQL private service:

```bash
mysql -h mysql-your-service -P 3306 -u your_user -p sap_ptw < sap_ptw.sql
```

## 4. Create PHP Web Service on Render

Create a new Web Service from the GitHub repository.

Use these settings:

- Language: Docker
- Dockerfile path: Dockerfile
- Branch: main, or your selected branch

Add these environment variables:

```text
DB_HOST=mysql-your-service
DB_PORT=3306
DB_NAME=sap_ptw
DB_USER=your_mysql_user
DB_PASS=your_mysql_password
```

## 5. Open from mobile and PC

When deployment succeeds, Render gives a public HTTPS URL like:

```text
https://your-service-name.onrender.com
```

Open that same URL on mobile, office PC, or home PC.

## Demo users shown on login page

```text
user1 / 123456
chp_ccr / 1234
bmd_ccr / 1234
```

## Local XAMPP defaults

The app still works locally with these defaults when environment variables are not set:

```text
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sap_ptw
DB_USER=root
DB_PASS=
```