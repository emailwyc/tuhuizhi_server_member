var fs = require('fs');
var path = require('path');
var mime = require('mime');
var staticVersion = require('../app/package.json').resource.static;
var limit = 8;
var picDist = './static/dist/img/';

function getFile(fpath) {
  return new Promise(function(resolve, reject) {
    console.log(fpath);
    var readStream = fs.createReadStream(fpath);
    var buffers = [];
    var nread = 0;
    readStream.on('data', function (chunk) {
        buffers.push(chunk);
        nread += chunk.length;
    });
    readStream.on('end', function () {
        var buffer = null;
        switch(buffers.length) {
            case 0: buffer = new Buffer(0);
                break;
            case 1: buffer = buffers[0];
                break;
            default:
                buffer = new Buffer(nread);
                for (var i = 0, pos = 0, l = buffers.length; i < l; i++) {
                    var chunk = buffers[i];
                    console.log(chunk);
                    chunk.copy(buffer, pos);
                    pos += chunk.length;
                }
            break;
        }
        var kb = buffer.length / 1024;
        var json = {src: fpath, hash: getHashDigest(buffer), kb: kb };
        if(kb < limit) {
          json.base64 = buffer.toString('base64');
          json.mime = mime.lookup(fpath);
        } else {
          var hashPath = getHashPath(json.src, json.hash);
          var wstream = fs.createWriteStream(picDist + path.basename(hashPath));
          wstream.write(buffer);
          wstream.end();
        }
        resolve(json);
    });
  });
}

function getHashDigest(buffer, hashType, digestType, maxLength) {
	hashType = hashType || "md5";
	maxLength = maxLength || 9999;
	var hash = require("crypto").createHash(hashType);
	hash.update(buffer);
	if (digestType === "base26" || digestType === "base32" || digestType === "base36" ||
	    digestType === "base49" || digestType === "base52" || digestType === "base58" ||
	    digestType === "base62" || digestType === "base64") {
		return encodeBufferToBase(hash.digest(), digestType.substr(4)).substr(0, maxLength);
	} else {
		return hash.digest(digestType || "hex").substr(0, maxLength);
	}
}
// 文件名添加版本
function getHashPath(src, strHash) {
  var dir = path.dirname(src);
  var ext = path.extname(src);
  var file = path.basename(src, ext);
  return dir + '/' + file + '.' + strHash.slice(-8) + ext;
}

function parseImg(page) {
  fs.readFile(page, function(err, html){
       if (err) throw err;
       var reImg = /src=\"(\.\.\/[^\"]*)\"\s/gi;
       html = '' + html;
       var march = null,
           promises = [],
           src = '',
           cache = {};
       while( march = reImg.exec(html) ) {
         src = march[1];
         if(cache[src]) {
           continue;
         }
         promises.push(getFile(src.replace('../.', '')));
         cache[src] = true;
       }
       Promise.all(promises).then(function(files){
         var maps = {};
         files.forEach(function(v){
           if(v.base64) {
             maps[v.src] = {base64: 'data:' + v.mime + ';base64,' + v.base64};
           } else {
             maps[v.src] = {src: getHashPath(v.src, v.hash)};
           }
         });
         console.log(maps);
         html = html.replace(reImg, function(a,src,c){
           src = src.replace('../.', '');
           var data = maps[src];
           var rtn = '';
           if (data.src) {
             rtn = 'src="' + src.replace(src, '//res.rtmap.com/static/dist/img/' + path.basename(data.src) ) + '"';
           } else {
             rtn = 'src="' + data.base64 + '"';
           }
           return rtn;
         });
         fs.writeFile(page, html, function(err, html){
           if(err) {
             console.log('写入失败');
           }
         });
       });
  });
}

// test
parseImg('./app/views/layout.html');
// parseImg('./app/views/taxi.html');
// parseImg('./app/views/taxi_order.html');
