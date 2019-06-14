# Use phusion/baseimage as base image. To make your builds reproducible, make
# sure you lock down to a specific version, not to `latest`!
# See https://github.com/phusion/baseimage-docker/releases for
# a list of version numbers.
FROM phusion/baseimage:0.9.22

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]

# set envs: HOME (required for proper composer setup), 
#           DEBIAN_FRONTEND (to disallow interactive from APT)
#           TERM=xterm (to inform console applications default terminal emulator)
ENV LC_ALL=en_US.UTF-8 \
    LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    HOME=/root \
    DEBIAN_FRONTEND=noninteractive \
    TERM=xterm


# prepare base image with general packages
RUN set -ex \
    # enable SSH and regenerate SSH keys

    && rm -f /etc/service/sshd/down \
    && /etc/my_init.d/00_regen_ssh_host_keys.sh \
    # uncomment following line to enable default insecure key
    #&& /usr/sbin/enable_insecure_key \

    # update base image and install general tools

    && apt-get update \
    && apt-get upgrade -y -o Dpkg::Options::="--force-confold" \
    && apt-get install mc htop nano vim graphviz iputils-ping netcat tmux git wget unzip make --no-install-recommends -y


# install ssmtp
RUN set -ex \
     && apt-get update \
     && apt-get install -y ssmtp --no-install-recommends \
     && echo "root=dockercms@gmail.com\nmailhub=smtp.gmail.com:587\nhostname=smtp.gmail.com:587\nUseSTARTTLS=YES\nAuthUser=dockercms@gmail.com\nAuthPass=zaq1xsw2cde3\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf


# add 'ondrej/php' PPA repo
RUN set -ex \
    && add-apt-repository ppa:ondrej/php --yes


# install php 7.1
RUN set -ex \
    && apt-get update \
    && apt-get install php7.1 php7.1-dev php7.1-cli php7.1-gd php7.1-json php7.1-intl php7.1-curl php7.1-fpm php7.1-bcmath php7.1-xml php7.1-opcache php7.1-mbstring php7.1-zip php7.1-pdo php7.1-pdo-mysql php7.1-bz2 php-xdebug php-soap php-ssh2 --no-install-recommends -y

# install Composer, Configure PHP-FPM
RUN set -ex \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && /usr/local/bin/composer -V \

    # configure PHP-FPM

    && mkdir /run/php \
    && cd /etc/php/7.1/fpm/pool.d \
    && { \
        echo '[global]'; \
        echo 'error_log = /proc/self/fd/2'; \
        echo 'daemonize = no'; \
        echo; \
        echo '[www]'; \
        echo 'listen = [::]:9000'; \
        echo '; if we send this to /proc/self/fd/1, it never appears'; \
        echo 'access.log = /proc/self/fd/2'; \
        echo 'user = dev'; \
        echo 'group = dev'; \
        echo; \
        echo 'clear_env = no'; \
        echo; \
        echo '; Ensure worker stdout and stderr are sent to the main error log.'; \
        echo 'catch_workers_output = yes'; \
    } | tee zz-docker.conf


# add configs and scripts dir to container
ADD ./configs /docker
ADD ./scripts /docker/scripts


# install XHProf from git repo
RUN set -ex \
    && cd /docker \
    && mkdir build -m 777 \
    && git clone https://github.com/RustJason/xhprof \
    && cd xhprof \
    && git checkout -b remote/origin/php7 \
    && cd extension \
    && phpize \
    && ./configure --with-php-config=/usr/bin/php-config \
    && make \
    && make install \
    && mkdir -m 777 -p /var/tmp/xhprof \
    && rm /docker/build/xhprof -rf


# setup services, final adjustments
RUN set -ex \
    # setup default user 'dev'

    && adduser --disabled-password --gecos '' dev \
    && passwd -u dev \
    && usermod -a -G www-data dev \
    && mkdir /home/dev/.ssh \
    && chmod 0700 /home/dev/.ssh \
    && chown dev:dev /home/dev/.ssh \
    && sed -i 's/#force_color_prompt=yes/force_color_prompt=yes/' /home/dev/.bashrc \
    # add ability to load env variables from external file (it will be filled up by init service)
    && echo "" >> /home/dev/.bashrc \
    && echo ". ~/.bash_env_setup" >> /home/dev/.bashrc \
    && touch /home/dev/.bash_env_setup \

    # add startup script to set proper rights on /*/.ssh/authorized_keys

    && cp /docker/scripts/01_ssh_fix.sh /etc/my_init.d/01_ssh_fix.sh \
    && chmod +x /etc/my_init.d/01_ssh_fix.sh \

    # add startup script to set cron tasks

    && cp /docker/scripts/02_cron_setup.sh /etc/my_init.d/02_cron_setup.sh \
    && chmod +x /etc/my_init.d/02_cron_setup.sh \

    # fix 'dev' user environment variables - add them to /home/dev/.bash_env_setup

    && cp /docker/scripts/03_dev_user_env.sh /etc/my_init.d/03_dev_user_env.sh \
    && chmod +x /etc/my_init.d/03_dev_user_env.sh \
    # directory for php-fpm service (look at baseimage-docker docs)

    && mkdir /etc/service/php7-fpm \

    # add services and configs

    && ln -s /docker/php7-fpm.sh /etc/service/php7-fpm/run \
    && chown root:root /etc/service/php7-fpm/run \
    && chmod +x /etc/service/php7-fpm/run \
    && ln -s /docker/php.ini /etc/php/7.1/cli/conf.d/000-docker-php.ini \
    && ln -s /docker/php.ini /etc/php/7.1/fpm/conf.d/000-docker-php.ini \
    && ln -s /docker/xdebug.ini /etc/php/7.1/fpm/conf.d/99-xdebug.ini \
    && ln -s /docker/xdebug.ini /etc/php/7.1/cli/conf.d/99-xdebug.ini \
    && ln -s /docker/xhprof.ini /etc/php/7.1/fpm/conf.d/99-xhprof.ini \
    && ln -s /docker/xhprof.ini /etc/php/7.1/cli/conf.d/99-xhprof.ini

# clean up APT
RUN set -ex \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


EXPOSE 9000
