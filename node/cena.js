var express = require('express');
var app = express();

var fs = require("fs");
var http = require('http').Server(app);
var io = require('socket.io')(http);
var mysql = require('mysql');
var request = require('request');
var forEach = require('async-foreach').forEach;
var jsesc = require('jsesc');
var moment = require('moment');
var path = require("path");
var util = require("util");
var bodyParser = require('body-parser');
const port = 2804;
const sever_url = "http://cenaapp.xyz:" + port;

var con = mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '<CeNA@@!!!@@8682376941>',
    database: 'cena',
    charset: 'utf8mb4',
    dateStrings: true
});
/*mysql connect connection*/
con.connect(function (err) {
    if (err)
        return console.log(err);
        else
        console.log("aya");
});


var sockets = {};


var host = 'http://cenaapp.xyz';
var api_url = host + '/api/';
var img_base_url = host + '/public/uploads/profile_picture/';

// var media_path = 'chat_images';
// var chat_media_path = __dirname + '/' + media_path;
//console.log(chat_media_path);

// var chat_fields = 'chats.*, IF(chats.file_url IS NULL, "", CONCAT("' + img_base_url + '", chats.file_url)) as file_url_full'

var user_fields = 'users.id as user_id, users.name,IF(users.profile_image IS NULL, "", CONCAT("' + img_base_url + '", users.profile_image)) as user_profile_image';

var receiver_fields = 'receiver.id as receiver_user_id, receiver.name as receiver_name, IF(receiver.profile_image IS NULL, "", CONCAT("' + img_base_url + '", receiver.profile_image)) as receiver_profile_image';

app.use(express.urlencoded({ extended: true }))

app.get('/chat', function (req, res) {
    res.sendFile(__dirname + '/chat.html');
});
app.get('/chat-other', function (req, res) {
    res.sendFile(__dirname + '/chat_other.html');
});
app.get('/chat-media', function (req, res) {
    res.sendFile(__dirname + '/chat_media.html');
});


/*socket update driver location*/
io.on('connection', function (socket) {
    //sockets[parseInt(socket.handshake.query.user_id)] = socket;

    if (!sockets[socket.handshake.query.user_id]) {
        sockets[socket.handshake.query.user_id] = [];
    }
    sockets[socket.handshake.query.user_id].push(socket);

    console.log('query', socket.handshake.query.user_id);
    var user_id = socket.handshake.query.user_id;
    // console.log("user_id",user_id);
    con.query("update users set is_online='Y' where id ='" + user_id + "'");
    console.log('a user connected', user_id);
    console.log('conn data:', socket.handshake.query);
    console.log('User Connected ',socket.handshake.query.user_id);

    socket.on('send_message', function (data, cb) {

        var receiver_id = data.receiver_id;
        var user_id = data.user_id;
        var group_id = genGroupId(user_id, data.receiver_id);
        var created_at = moment().utc().format('YYYY-MM-DD HH:mm:ss');
        console.log('---------data----------', data);
        console.log('---------userID----------', user_id);
        console.log('---------rcvrID----------', receiver_id);
        var blocked_by_me_data = checkBlockedByMe(user_id, receiver_id);
        var blocked_by_other_data = checkBlockedByOther(user_id, receiver_id);
        Promise.all([blocked_by_me_data, blocked_by_other_data]).then((values) => {
            var is_blocked_by_me = values[0].is_blocked_by_me;
            var is_blocked_by_other = values[1].is_blocked_by_other;
            if (is_blocked_by_me || is_blocked_by_other) {
                var message = (is_blocked_by_me) ? 'User is blocked by you.' : 'User has blocked you.'
                var multi_response = {
                    status: true,
                    message: message,
                    data: [],
                };
                socket.emit('receive_message', multi_response);
            } else {
                

                var type = (typeof data.type != 'undefined') ? data.type : 'TEXT';
                var lat = (typeof data.lat != 'undefined') ? data.lat : null;
                var lng = (typeof data.lng != 'undefined') ? data.lng : null;
                var file_url = (typeof data.file_url != 'undefined') ? data.file_url : null;


                var sql = "INSERT INTO chats(group_id,user_id,receiver_id,type,file_url,message,created_at,updated_at) values(?,?,?,?,?,?,?,?)";
                con.query(sql, [group_id, user_id, data.receiver_id, type, file_url, data.message, created_at, created_at], function (err, result) {
                    if (err) {
                        console.log("ERROR", err)
                    } else {
                        var chat_id = result.insertId;
                        var sql = "SELECT chats.*, " + user_fields + ", " + receiver_fields + " FROM chats LEFT JOIN users ON chats.user_id=users.id LEFT JOIN users receiver ON chats.receiver_id=receiver.id WHERE chats.id= " + chat_id;
                        con.query(sql, (err, result) => {
                            if (err) {
                                console.log(err);
                            } else {
                                chat_data_row = result[0];
                                var multi_response = {
                                    status: true,
                                    message: 'Message sent.',
                                    data: chat_data_row,
                                };
                                socket.emit('receive_message', multi_response);
                                console.log(multi_response);

                                var receive_response = {
                                    status: true,
                                    message: 'Message received.',
                                    data: chat_data_row,
                                };
                                //console.log(receiver_id, sockets);
                                if (sockets[receiver_id] != undefined) {
                                    //console.log(receiver_id);
                                    for (var index in sockets[receiver_id]) {
                                        sockets[receiver_id][index].emit('receive_message', receive_response);
                                    }
                                }

                                //notification

                                 console.log("here");
                                if(data.type=='IMAGE'){
                                    msg = 'Sent Image';
                                }
                                else{
                                    msg = data.message;
                                }
                                console.log(host+'/api/v2/send-chat-notification?user_id='+data.receiver_id+'&message='+msg+'&other_user_id='+data.user_id);
                                request(host+'/api/v2/send-chat-notification?user_id='+data.receiver_id+'&message='+msg+'&other_user_id='+data.user_id, { json: true }, (err, res) => {
                                  if (err) { console.log(err); }
                                });
                            }
                        });
                    }
                });
            }


        });
    });



    socket.on('get_chats', function (data, cb) {
        
        console.log('user_id', user_id);
        
        var unread_count = "(SELECT COUNT(chat_c.id) FROM chats AS chat_c WHERE (chat_c.delete_user_id != " + user_id + " OR chat_c.delete_user_id IS NULL) AND ((chat_c.user_id=chats.user_id AND chat_c.receiver_id=chats.receiver_id) OR (chat_c.user_id=chats.receiver_id AND chat_c.receiver_id=chats.user_id)) AND chat_c.user_id !=" + user_id + " AND is_read='0') AS  unread_count";

        // var unread_count = "SELECT COUNT(chats.id) AS  unread_count FROM chats WHERE (chats.delete_user_id != " + user_id + " OR chats.delete_user_id IS NULL) AND chats.receiver_id =" + user_id + " AND is_read='0'";

        var sql = "SELECT chats.*, " + user_fields + ", " + receiver_fields + ", " + unread_count + ", " + "if((SELECT count(*) FROM `reported_users` WHERE  reported_users.user_id =" + user_id + " AND reported_users.reported_user_id=chats.receiver_id  )>0,true,false) as is_report,if((SELECT count(*) FROM `blocked_users` WHERE  blocked_users.user_id = chats.user_id AND blocked_users.other_user_id=chats.receiver_id  )>0,true,false) as blocked_by_me,if((SELECT count(*) FROM `blocked_users` WHERE  blocked_users.user_id = chats.receiver_id AND blocked_users.other_user_id= chats.user_id  )>0,true,false) as blocked_by_receiver  FROM chats LEFT JOIN users ON chats.user_id=users.id LEFT JOIN users receiver ON chats.receiver_id=receiver.id  WHERE (chats.user_id=" + user_id + " OR chats.receiver_id=" + user_id + ") AND (chats.delete_user_id != " + user_id + " OR chats.delete_user_id IS NULL) AND chats.id IN (SELECT MAX(chats.id) FROM chats GROUP BY chats.group_id) ORDER BY chats.id DESC";
        console.log(sql);
        con.query(sql, (err, result) => {
            if (err) {
                console.log(err);
            } else {
                var response = {
                    status: true,
                    message: 'Chats',
                    data: result,
                };
                console.log(response);
                socket.emit('get_chats_response', response);
            }
        });
    });

    socket.on('get_message_history', function (data, cb) {
        console.log(data.group_id);
        var group_id = data.group_id;
        var last_id = data.last_id || 0;
        var order = data.order || 'DESC';
                    
        var group_exp = group_id.split('-');
        if (group_exp!=undefined) {
            var other_user_id = (group_exp[0] == user_id) ? group_exp[1] : group_exp[0];
        }else{
            var other_user_id = '';
        }
        
        console.log(other_user_id);
        console.log(user_id);

        var count_sql = "SELECT count(chats.id) AS total_count FROM chats WHERE (delete_user_id !=" + user_id + " OR delete_user_id IS NULL) AND group_id='" + group_id + "'";
        console.log("count_sql"+count_sql);

        con.query(count_sql, (err, count_result) => {
            if (err) {
                console.log(err);
            } else {
                var total_records = (typeof count_result[0] != 'undefined' && typeof count_result[0].total_count != 'undefined') ? count_result[0].total_count : 0;
                console.log("total_records" + total_records);
                if (total_records == 0) {

                    var response_data = {
                        status: true,
                        message: 'get_message_history_response',
                        data: [],
                        total_records: total_records,
                        current_page: 1,
                        total_page: 1
                    };
                    console.log("response DAta" + response_data);
                    socket.emit('get_message_history_response', response_data);
                } else {
                    //updating message read

                    con.query(`update chats set is_read='1' where group_id='${group_id}'  and receiver_id = ${user_id} and is_read='0'`,function(err,res){});

                    var page_limit = total_records;
                    // var page_limit = 10;
                    var total_page = Math.ceil(total_records / page_limit);
                    //var offset = Math.ceil(page_no * page_limit) - page_limit;
                    var last_con = "";
                    if (last_id !== 0) {
                        if (order == 'DESC') {
                            last_con = "AND chats.id < " + last_id;
                        } else {
                            last_con = "AND chats.id > " + last_id;
                        }
                    }

                    var sql = "SELECT chats.*, " + user_fields + ", " + receiver_fields + " FROM chats LEFT JOIN users ON chats.user_id=users.id LEFT JOIN users receiver ON chats.receiver_id=receiver.id WHERE (chats.delete_user_id !=" + user_id + " OR chats.delete_user_id IS NULL) AND group_id='" + group_id + "' " + last_con + " ORDER BY chats.id DESC LIMIT " + page_limit;

                    console.log("log1" + sql);

                    con.query(sql, (err, result) => {
                        if (err) {
                            console.log(err);
                        } else {

                            var first_data = new Promise(function (resolve, reject) {
                                if (last_id == 0 || last_id == null) {
                                    var reverser_order = (order == 'DESC') ? 'ASC' : 'DESC';
                                    var sql = "SELECT id FROM chats WHERE (delete_user_id != " + user_id + " OR chats.delete_user_id IS NULL) AND group_id='" + group_id + "' ORDER BY chats.id " + reverser_order + " LIMIT 1";
                                    con.query(sql, (err, result_first) => {
                                        if (err) {
                                            console.log(err);
                                        } else {
                                            resolve({ first_id: result_first[0].id });
                                        }
                                    });
                                } else {
                                    resolve({ first_id: null });
                                }

                            });

                            var blocked_by_me_data = checkBlockedByMe(user_id, other_user_id);
                            var blocked_by_other_data = checkBlockedByOther(user_id, other_user_id);

                            Promise.all([first_data, blocked_by_me_data, blocked_by_other_data]).then((values) => {
                                console.log('here----------', values);
                                console.log('here123----------', result);
                                var response_data = {
                                    status: true,
                                    message: 'user_thread_response',
                                    data: result,
                                    total_records: total_records,
                                    current_page: 1,
                                    total_page: total_page,
                                    first_id: values[0].first_id,
                                    is_blocked_by_me: values[1].is_blocked_by_me,
                                    is_blocked_by_other: values[2].is_blocked_by_other,
                                };
                                console.log('last response ----------', response_data);
                                socket.emit('get_message_history_response', response_data);
                            });
                        }
                    });
                }
            }
        });
    });


    socket.on('read_message_update', function (data, cb) {
        var user_id = data.user_id;
        var receiver_id = data.receiver_id;
        // var sender_id = data.sender_id;
        //var group_id = (sender_id < receiver_id) ? sender_id + '-' + receiver_id : receiver_id + '-' + sender_id;
        console.log('Data request --- - ', receiver_id);

        // if (str.length > 0) {

        //    // con.query("update chats set is_read='1' where id IN (" + str + ") ");
        //    con.query("update chats set is_read='1' where receiver_id =" + receiver_id);
        //     var response_data = { 'message': 'Records has been updated successfully', 'status': 'true' };
        //     socket.emit('read_success', response_data);
        // }
        var sql = "update chats set is_read='1' where receiver_id =" + receiver_id + " and user_id=" + user_id;
         con.query(sql, (err, result) => {
            if (err) {
                console.log(err);
            } else {
                var response = {
                    status: true,
                    message: 'Records has been updated successfully.',
                    
                };
                socket.emit('read_success', response);
            }
        });


    });


    socket.on('total_unread', function (data, cb) {
        var sql = "SELECT COUNT(chats.id) AS  unread_count FROM chats WHERE (chats.delete_user_id != " + user_id + " OR chats.delete_user_id IS NULL) AND chats.receiver_id =" + user_id + " AND is_read='0'";
        con.query(sql, (err, result) => {
            if (err) {
                console.log(err);
            } else {
                var response = {
                    status: true,
                    message: 'Total Unread Count',
                    data: {
                        unread_count: result[0].unread_count
                    }
                };
                socket.emit('total_unread_response', response);
            }
        });
    });


    // socket.on('disconnect', function (eee_dis) {

    //     console.log('dis error', eee_dis);
    // });

      socket.on('disconnect', function (eee_dis) {
        console.log("disconnect = ", user_id);
        con.query("update users set is_online='N' where id ='" + user_id + "'");
        console.log('dis error', eee_dis);
        for (var data in sockets[socket.handshake.query.user_id]) {
          if (socket.id == sockets[socket.handshake.query.user_id][data].id) {
            console.log('sockethandshakequeryuser_id ', socket.handshake.query.user_id);
            sockets[socket.handshake.query.user_id].splice(data, 1);
          }
        }
    });

    function genGroupId(sender_id, receiver_id) {
    console.log(sender_id,receiver_id);
        var group_id = (sender_id > receiver_id) ? sender_id + '-' + receiver_id : receiver_id + '-' + sender_id;
        console.log("grpId",group_id);
        return group_id;
    }

    function randomName() {

        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text + '_' + Date.now();
    }

    function checkBlockedByMe(user_id, other_user_id) {
        return new Promise(function (resolve, reject) {
             var sql = "SELECT count(*) as is_blocked FROM blocked_users WHERE (user_id = " + user_id + ") AND other_user_id='" + other_user_id + "' ";
             console.log("By Me----",sql);
            con.query(sql, (err, result_first) => {
                if (err) {
                   resolve({ is_blocked_by_me: false });
                } else {
                    if(result_first[0].is_blocked>0){
                        resolve({ is_blocked_by_me: true });

                    }
                    else{
                        resolve({ is_blocked_by_me: false });
                    }
                }
            });
        });
    }

    function checkBlockedByOther(user_id, other_user_id) {
        return new Promise(function (resolve, reject) {
            var sql = "SELECT count(*) as is_blocked FROM blocked_users WHERE (user_id = " + other_user_id + ") AND other_user_id='" + user_id + "' ";
            console.log("By other----",sql);
            con.query(sql, (err, result_first) => {
                if (err) {
                   resolve({ is_blocked_by_other: false });
                } else {
                    if(result_first[0].is_blocked>0){
                        resolve({ is_blocked_by_other: true });

                    }
                    else{
                        resolve({ is_blocked_by_other: false });
                    }
                }
            });
            //resolve({ is_blocked_by_other: false });
        });
    }

});

/*server connection*/
http.listen(port, function () {
    console.log('listening on   *:', port);
});
