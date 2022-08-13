# 🌐 [Talus Web Backend](http://34.252.141.173)
- Provides Web Dashboard and Backend APIs to work with ***CI/CD Pipeline***.
- [Build Mac - Environment Setup](https://github.com/TalusStudio-Packages/Build-Mac-Environment)
- [Build Mac - Jenkins Setup](https://github.com/TalusStudio-Packages/Jenkins-Docs)
- [Google Captcha Key Generation](https://www.google.com/recaptcha/admin/create)


# 💿 Production Environment Setup
- Required OS >= ***Ubuntu 20.04***

1. Run Script
```
sh init_server.sh
```
2. ***Crontab*** Settings (`crontab -e`)
```
* * * * * cd /var/www/html/TalusWebBackend && /usr/bin/php8.1 artisan schedule:run >> /dev/null 2>&1
```

3. ***MySQL*** Root Password Settings
```
sudo mysql --user=root mysql
mysql> UPDATE mysql.user SET authentication_string=null WHERE User='root';
mysql> flush privileges;
mysql> ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password_here';
mysql> flush privileges;
mysql> create database laravel;
mysql> exit;
```

4. ***Apache*** Settings (`/etc/apache2/apache2.conf`)
```php
  <Directory /var/www/>
      Options Indexes FollowSymLinks
      AllowOverride all
      Require all granted
  </Directory>
```
5. Set `DocumentRoot` path in `/etc/apache2/sites-enabled/000-default.conf` with `/var/www/html/TalusWebBackend/public`
6. Restart Apache `sudo service apache2 restart`
7. Populate `.env` file on project root
8. Run Script
```
sh init_project.sh
```

# 🔑 Apps API
- Optional parameters marked with `?`

```
GET    |   api/get-app?id={id}
POST   |   api/create-app?app_icon={icon?}&app_name={appName}&project_name={projectName}&app_bundle={appBundle}&fb_app_id={fbAppId?}&ga_id={gaID?}&ga_secret={gaSecret?}
POST   |   api/update-app?id={id}&fb_app_id={fbAppID?}&ga_id={gaID?}&ga_secret={gaSecret?}
```

# 🔑 [App Store Connect API](https://developer.apple.com/documentation/appstoreconnectapi)
```
GET    |   api/appstoreconnect/get-token
GET    |   api/appstoreconnect/get-full-info
GET    |   api/appstoreconnect/get-app-list
GET    |   api/appstoreconnect/get-build-list
POST   |   api/appstoreconnect/create-bundle?bundle_id={bundleId}&bundle_name={bundleName}
```

# 🔑 [Jenkins API](https://github.com/jenkinsci/pipeline-stage-view-plugin/tree/master/rest-api)
```
GET    |   api/jenkins/get-job?id={id}
GET    |   api/jenkins/get-job-list
GET    |   api/jenkins/get-build-list?id={id}
GET    |   api/jenkins/get-latest-build-info?id={id}
POST   |   api/jenkins/stop-job?id={id}&build_number={buildNumber}
POST   |   api/jenkins/build-job?id={id}&platform={platform}&storeVersion={storeVersion}
```

# 🔑 GitHub API
```
GET    |   api/github/get-repositories
GET    |   api/github/get-repository?project_name={projectName}
POST   |   api/github/create-repository?project_name={projectName}
```

# 🔑 Packages API
```
GET   |   api/get-package?package_id={id}
GET   |   api/get-packages
POST  |   api/update-package?package_id={id}&hash={hash}
```
