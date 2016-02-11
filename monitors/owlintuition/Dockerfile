FROM node:5.5

ADD package.json /app/package.json
ADD index.js /app/index.js

RUN cd /app; npm install

EXPOSE 80

CMD ["npm", "start"]
