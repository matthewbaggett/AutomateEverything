FROM node:5.5

RUN mkdir /app
ADD package.json /app/package.json

RUN cd /app && npm install
WORKDIR /app
ADD . /app
#RUN ls -lah /app && du -sch /app

EXPOSE 80

CMD ["npm", "start"]
