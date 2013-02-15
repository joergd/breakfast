
// inspects the url to determine which edition we're in.
// editions have a /001 or /002 etc in the url
function getIssue() {
  var url;
  var regex=/\d{3}/;
  url = '' + this.location;
  var match = url.match(regex);
  
  return match;
}

function setIssue() {
  var date=new Date();
  date.setTime(date.getTime()+(365*24*60*60*1000)) ;
  document.cookie = 'issue=' + getIssue() + '; domain=www.ak47.tv; expires=' + date.toGMTString() + ';';
  //setCookie("issue", getIssue(), date, "www.ak47.tv");
}