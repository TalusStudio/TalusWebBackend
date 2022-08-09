# 🌐 [Talus Web Backend](http://34.252.141.173)
- Provides Web Dashboard and Backend APIs to work with ***CI/CD Pipeline***.
- Production server running on ***AWS Lightsail***.
- [Build Mac Environment Setup](https://github.com/TalusStudio-Packages/Build-Mac-Environment)
- [Google Captcha Key Generation](https://www.google.com/recaptcha/admin/create)
- [Postman](https://www.postman.com)

# 💿 Environment Setup
- Required OS >= ***Ubuntu 20.04***

- Run Script
```
sh init_server.sh
```
- ***Crontab*** Settings (`crontab -e`)
```
* * * * * cd /var/www/html/TalusWebBackend && /usr/bin/php8.1 artisan schedule:run >> /dev/null 2>&1
```

- ***MySQL*** Settings
```
sudo mysql --user=root mysql
mysql> UPDATE mysql.user SET authentication_string=null WHERE User='root';
mysql> flush privileges;
mysql> ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password_here';
mysql> flush privileges;
mysql> create database laravel;
mysql> exit;
```

- ***Apache*** Settings (`/etc/apache2/apache2.conf`)
```php
  <Directory /var/www/>
      Options Indexes FollowSymLinks
      AllowOverride all
      Require all granted
  </Directory>
```
- Set `DocumentRoot` path in `/etc/apache2/sites-enabled/000-default.conf` with `/var/www/html/TalusWebBackend/public`

# 🔑 [App Store Connect API](https://developer.apple.com/documentation/appstoreconnectapi)
```
GET   |   api/appstoreconnect/get-token
GET   |   api/appstoreconnect/get-full-info
GET   |   api/appstoreconnect/get-app-list
GET   |   api/appstoreconnect/get-app-list/{id}
GET   |   api/appstoreconnect/create-bundle?bundle_id={bundleId}&bundle_name={bundleName}
```

# 🔑 [Jenkins API](https://github.com/jenkinsci/pipeline-stage-view-plugin/tree/master/rest-api)
```
GET    |   api/jenkins/get-job-list
GET    |   api/jenkins/get-job/{projectName}
GET    |   api/jenkins/get-build-list/{projectName}
GET    |   api/jenkins/get-latest-build-info/{projectName}
POST   |   api/jenkins/stop-job/{projectName}/{buildNumber}
```

# 🔑 GitHub API
```
GET    |   api/github/get-repositories
GET    |   api/github/get-repository/{id}
```

# 🔑 Apps API
```
GET    |   api/get-app/{id}
```


# 🔑 Packages API
```
GET   |   api/get-package/{id}
GET   |   api/get-packages
POST  |   api/update-package/{id}/{hash}
```
