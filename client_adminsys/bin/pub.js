var fs = require('fs');
var path = require('path');
fs.mkdirSync('./static/dist/html/');

var staticVersion = require('../app/package.json').resource.static;

fs.readFile('./html/nannvshen/index.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     // console.log(html);
     fs.writeFile('./static/dist/html/index.html', html, function(err, html){});
});

fs.readFile('./html/nannvshen/list.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     // console.log(html);
     fs.writeFile('./static/dist/html/list.html', html, function(err, html){});
});

fs.readFile('./html/nannvshen/confirm.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     //      // console.log(html);
     // console.log(html);
     fs.writeFile('./static/dist/html/confirm.html', html, function(err, html){});
});

fs.readFile('./html/nannvshen/nanshen_terminal.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     //      // console.log(html);
     // console.log(html);
     fs.writeFile('./static/dist/html/nanshen_terminal.html', html, function(err, html){});
});

fs.readFile('./html/nannvshen/terminalinfo.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     //      // console.log(html);
     // console.log(html);
     fs.writeFile('./static/dist/html/terminalinfo.html', html, function(err, html){});
});

fs.readFile('./html/nannvshen/followpage.html', function(err, html){
     if (err) throw err;
     var reImg = /src=\"(\.\.\/.*)\"/gi;
     html = '' + html;
     html = html.replace(reImg, function(a,path,c){
       return 'src="' + path.replace('../../static', 'http://res.rtmap.com/static/dist') + '"';
     });
     html = html.replace(/dist\/js/gi, 'dist/' + staticVersion + '/js');
     html = html.replace(/dist\/css/gi, 'dist/' + staticVersion + '/css');
     //      // console.log(html);
     // console.log(html);
     fs.writeFile('./static/dist/html/followpage.html', html, function(err, html){});
});
