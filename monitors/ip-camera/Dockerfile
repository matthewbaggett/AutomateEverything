FROM node:5.6

RUN echo "deb http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list
RUN echo "deb-src http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list
RUN apt-get update -qq && \
    apt-get -yq --force-yes install deb-multimedia-keyring && \
    apt-get -yq remove ffmpeg && \
    apt-get -yq --force-yes install \
        build-essential \
        handbrake-cli \
        libav-tools \
        build-essential \
        libmp3lame-dev \
        libvorbis-dev \
        libtheora-dev \
        libspeex-dev \
        yasm \
        pkg-config \
        libfaac-dev \
        libopenjpeg-dev \
        libx264-dev \
	libvpx-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir /app
RUN mkdir /app/ffmpeg

WORKDIR /app

RUN cd /app/ffmpeg && \
    wget http://ffmpeg.org/releases/ffmpeg-2.7.2.tar.bz2 && \
    tar xvjf ffmpeg-2.7.2.tar.bz2 && \
    cd /app/ffmpeg/ffmpeg-2.7.2 && \
    ./configure \
        --enable-gpl \
        --enable-postproc \
        --enable-swscale \
        --enable-avfilter \
        --enable-libmp3lame \
        --enable-libvorbis \
        --enable-libtheora \
        --enable-libx264 \
        --enable-libspeex \
        --enable-shared \
        --enable-pthreads \
        --enable-libopenjpeg \
        --enable-libfaac \
        --enable-nonfree \
        --enable-libvpx \
        --enable-libvorbis && \
    make && \
    make install && \
    /sbin/ldconfig && \
    rm -Rf /app/ffmpeg

ADD package.json /app/package.json
ADD index.js /app/index.js

RUN cd /app; npm install

EXPOSE 80

CMD ["npm", "start"]
