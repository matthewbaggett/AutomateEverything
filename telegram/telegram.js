var TelegramBot = require('node-telegram-bot-api');

var token = '167866972:AAH-e5PnZlGuhbL_8ySMohJTakdhHqf5UWc';
// Setup polling way
var bot = new TelegramBot(token, {polling: true});

// Matches /echo [whatever]
bot.on('message', function (msg) {
    //var now = Math.floor(new Date() / 1000);
    //var ago = now - msg.date;
    console.log( " seconds ago: " +  "Saw " + msg.from.username + " say " + match[1]);
});

bot.onText(/\/echo (.+)/, function (msg, match) {
    var fromId = msg.from.id;
    var resp = match[1];
    bot.sendMessage(fromId, resp);
});

bot.sendMessage()

// Any kind of message
//bot.on('message', function (msg) {
//    var chatId = msg.chat.id;
//    // photo can be: a file path, a stream or a Telegram file_id
//    var photo = 'cats.jpg';
//    bot.sendPhoto(chatId, photo, {caption: 'Lovely kittens'});
//});