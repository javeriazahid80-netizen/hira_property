# PHP aur Apache ka official environment lein
FROM php:8.1-apache

# MySQL se connect karne ke liye mysqli extension install karein
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Project ka saara code container ke andar copy karein
COPY . /var/www/html/

# Apache server ke permissions set karein
RUN chown -R www-data:www-data /var/www/html/

# Port 80 ko open karein web traffic ke liye
EXPOSE 80