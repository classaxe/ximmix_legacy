function details(report_name,ID,height,width){
  popWin('./?mode=details&report_name='+report_name+'&ID='+ID,'pop_'+report_name,'scrollbars=0,resizable=1',width,height,'centre');
}

// ************************************
// * popWin()                         *
// ************************************
function popWin(theURL,winName,features,windowx,windowy,centre) {
  if (centre == "centre") {
	var availx;
	var availy;
    var posx;
    var posy;
	availx = screen.availWidth;
	availy = screen.availHeight;
    posx = (availx - windowx)/2;
    posy = (availy - windowy)/2;
    var theWin = window.open(theURL,winName,features+',width='+windowx+',height='+windowy+',left='+posx+',top='+posy);
  } else {
    var theWin = window.open(theURL,winName,features+',width='+windowx+',height='+windowy+',left=25,top=25');
  }
  theWin.focus();
}



function findThisPage() {
  return (location.search.length ? "./"+location.search : location.pathname.substring(1+location.pathname.lastIndexOf('/')));
}


function openFolderInTree(linkID) {
  try {
    if (linkID==null) {
      linkID = findThisPage();
    }
    var folderObj;
    folderObj = parent.treeframe.findObj(linkID);
    folderObj.forceOpeningOfAncestorFolders();
    if (!folderObj.isOpen) {
      parent.treeframe.clickOnNodeObj(folderObj);
    }
  }
  catch(e) {
  }
}

function writeNav() {
  try {
    var thisPage =	findThisPage();
    var folderObj = 	parent.treeframe.findObj(thisPage);
    var parentPage =	(folderObj.parentObj != null && folderObj.parentObj.hreference!='javascript:parent.op()' ? folderObj.parentObj.hreference : false);
    var previous =    (folderObj.id-1>0 ? parent.treeframe.indexOfEntries[folderObj.id-1].getID() : false);
    var next =        (folderObj.id+1<parent.treeframe.indexOfEntries.length ? parent.treeframe.indexOfEntries[folderObj.id+1].getID() : false);

    top.nav['up'] = 	parentPage;
    top.nav['sync'] =	thisPage;
    top.nav['back'] =	previous
    top.nav['next'] =	next;

    top.navigation.nav_setup();
  }
  catch(e) {
  }
}

function writeBreadcrumbs(thisPage) {
  try {
    if (thisPage==null) {
      thisPage =	findThisPage();
    }
    return("[ Help &gt; "+writeBreadcrumb(thisPage).substring(6)+" ]");
  }
  catch(e) {
  }
}

function writeBreadcrumb(thePage) {
  try {
    thisPage =		findThisPage();
    var folderObj = 	parent.treeframe.findObj(thePage);
    var folderPath =  writeBreadcrumbsPath(thePage);
    var out =		     " &gt; " +(thePage==thisPage ?
                        "<a title='Current page: "+folderPath+"' onmouseover='return omo(\"Current page: "+folderPath+"\")' onmouseout='return omo(0)' href='javascript:void nav_link(\""+thePage+"\")' style='color: "+parent.treeframe.HIGHLIGHT_COLOR+"; background-color: "+parent.treeframe.HIGHLIGHT_BG+";'>"+folderObj.desc+"</a>"
                     :
                        "<a title='Go to "+folderPath+"' onmouseover='return omo(\"Go to "+folderPath+"\")' onmouseout='return omo(0)' href='javascript:void nav_link(\""+thePage+"\")' style='color: #000000;'>"+folderObj.desc+"</a>"
                     )
    if (folderObj.parentObj != null && folderObj.parentObj.hreference!='javascript:parent.op()') {
      out= writeBreadcrumb(folderObj.parentObj.hreference)+out
    }
    return out;
  }
  catch(e) {
  }
}


function writeBreadcrumbsPath(thisPage) {
  if (thisPage==null) {
    thisPage =	       findThisPage();
  }
  return "Help "+writeBreadcrumbPath(thisPage);
}

function writeBreadcrumbPath(thePage) {
  try{
    var thisPage =	findThisPage();
    var folderObj = 	parent.treeframe.findObj(thePage);
    var out =		" &gt; "+folderObj.desc;
    if (folderObj.parentObj != null && folderObj.parentObj.hreference!='javascript:parent.op()') {
      out= writeBreadcrumbPath(folderObj.parentObj.hreference)+out
    }
    return out;
  }
  catch(e) {
  }
}


function writeLink(thisPage,text){
  try {
    document.write(
      "<a href='javascript:void nav_link(\""+thisPage+"\");' "
	  +"onmouseover='return omo(\"Go to "+writeBreadcrumbsPath(thisPage)+"\")' onmouseout='return omo(0)' "
	  +"title='Go to "+writeBreadcrumbsPath(thisPage)+"'>"
	  +"<b>"+(text==null ? parent.treeframe.findObj(thisPage).desc : text) +"</b></a>");
  }
  catch(e) {
  }
}


function writeFooter() {
  document.write("<hr><p class='footer'>"+writeBreadcrumbs()+"</p>");
  writeNav();
}

function nav_link(url) {
// Split bookmark away from url, eg: user_personal-folders_folder-tree.html#folder
  link=url.match(/([^\#]+)/)[0];

//  top.navigation.collapseTree();	// Seems to cause problems with IE
  top.navigation.loadSynchPage(link);
  top.basefrm.location = url;

}

function omo(what) {
  window.status =	((what)?(what):(""));
  return true;
}

function loadSynchPage(linkID) {
  try {
    docObj = parent.treeframe.findObj(linkID);
    docObj.forceOpeningOfAncestorFolders();
    parent.treeframe.clickOnLink(linkID,docObj.link,'basefrm');
  }
  catch(e) {
//    alert("yes");
  }
}

function collapseTree() {
  parent.treeframe.clickOnNodeObj(parent.treeframe.foldersTree);	//hide all folders
  parent.treeframe.clickOnNodeObj(parent.treeframe.foldersTree);	//restore first level
}

function tool(img){
  this.off =	new Image();	this.off.src= './img/?mode=sysimg&img='+img+'_n.gif';
  this.on =	new Image();	this.on.src=  './img/?mode=sysimg&img='+img+'_o.gif';
  this.gray =	new Image();	this.gray.src='./img/?mode=sysimg&img='+img+'_i.gif';
}

function nav_setup() {
  document.next.src = ((top.nav['next']=='')? (tools.next.gray.src): (tools.next.off.src));
  document.back.src = ((top.nav['back']=='')? (tools.back.gray.src): (tools.back.off.src));
  document.up.src =   ((top.nav['up']=='')?   (tools.up.gray.src):   (tools.up.off.src));
  if (top.page!="") {
    loadSynchPage(top.page);
    top.page="";
  }
}

function nav_go(button) {
  if (top.nav[button] != '') {
//    collapseTree();		// Causes problems in IE6
    loadSynchPage(top.nav[button]);
    top.basefrm.location = top.nav[button];
  }
}

function nav_over(button) {
  switch(button) {
    case "back":
      msg =	"Previous Topic"
    break;
    case "next":
      msg =	"Next Topic"
    break;
    case "up":
      msg =	"Previous Level"
    break;
  }
  if (top.nav[button] != '') {
    document[button].src =	tools[button].on.src;
    window.status =		"Go to "+msg;
    document[button].title =	"Go to "+msg;
  }
  else {
    window.status =		"(Cannot go to "+msg+")";
    document[button].title =	"(Cannot go to "+msg+")";
  }
}

function nav_out(button) {
  if (top.nav[button] != '') {
    document[button].src = tools[button].off.src;
  }
  window.status = '';
}
