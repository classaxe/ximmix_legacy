// 1.0.2

/*
Version History:
  1.0.2 (2015-02-02)
    1) Now with unix-style line endings
  1.0.1 (2010-05-10)
    1) Changes to conform to LINT
  1.0.0 (2010-03-14)
    1) Initial release
       Combines altered versions of the following files, all created by
       Mircho Mirev - mo /mo@momche.net/
       See http://momche.net/publish/article.php?page=rssload
       Original scripts: modomext.js, xmlextras.js, modomt.js, morss.js
*/

// ************************************
// * External References (for JSLint) *
// ************************************
/*jslint browser: true, evil: true, maxerr: 200 */

/*global cDomExtensionManager:true */

// Browser:
/*global alert, window, ActiveXObject, Document, DOMParser, XMLSerializer */

//   modomext.js
function cDomExtension( hParent, aSelectors, hInitFunction ){
  this.hParent = hParent;
  this.aSelectors = aSelectors;
  this.hInitFunction = hInitFunction;
}

cDomExtensionManager = { aExtensions : [] };

cDomExtensionManager.register = function( hDomExtension ){
  cDomExtensionManager.aExtensions.push( hDomExtension );
};

cDomExtensionManager.initSelector = function( hParent, sSelector, hInitFunction ){
  var hGroup, hSelectorRegEx, hAttributeRegEx, aSelectorData, aAttributeData,
      sAttribute, nI;
  hSelectorRegEx = /([a-z0-9_]*)\[?([^\]]*)\]?/i;
  hAttributeRegEx = /([a-z0-9_]*)([\*\^\$]?)(=?)(([a-z0-9_=]*))/i;
  if( hSelectorRegEx.test( sSelector ) && !/[@#\.]/.test( sSelector ) ){
    aSelectorData = hSelectorRegEx.exec( sSelector );
    if( aSelectorData[ 1 ] != '' ){
      hGroup  = hParent.getElementsByTagName( aSelectorData[ 1 ].toLowerCase() );
      for( nI = 0; nI < hGroup.length; nI ++ ){
        hGroup[ nI ].markExt = true;
      }
      for( nI = 0; nI < hGroup.length; nI ++ ){
        if(!hGroup[ nI ].markExt){
          continue;
        }
        else {
          hGroup[ nI ].markExt = false;
        }
        if( aSelectorData[ 2 ] == '' ){
          if( hGroup[ nI ].tagName.toLowerCase() == aSelectorData[ 1 ].toLowerCase()  ){
            hInitFunction( hGroup[ nI ] );
          }
        }
        else {
          aAttributeData = hAttributeRegEx.exec( aSelectorData[ 2 ] );
          if( aAttributeData[ 1 ] == 'class' ){
            sAttribute = hGroup[ nI ].className;
          }
          else{
            sAttribute = hGroup[ nI ].getAttribute( aAttributeData[ 1 ] );
          }
          if( sAttribute !== null && sAttribute.length > 0 ){
            if( aAttributeData[ 3 ] == '=' ){
              if( aAttributeData[ 2 ] == '' ){
                if( sAttribute == aAttributeData[4] ){
                  hInitFunction( hGroup[ nI ] );
                }
              }
              else{
                switch( aAttributeData[ 2 ] ){
                  case '^' :
                    if( sAttribute.indexOf( aAttributeData[ 4 ] ) === 0 ){
                      hInitFunction( hGroup[ nI ] );
                    }
                  break;
                  case '$' :
                    if( sAttribute.lastIndexOf( aAttributeData[ 4 ] ) == sAttribute.length - aAttributeData[ 4 ].length ){
                      hInitFunction( hGroup[ nI ] );
                    }
                  break;
                  case '*' :
                    if( sAttribute.indexOf( aAttributeData[ 4 ] ) >= 0 ){
                      hInitFunction( hGroup[ nI ] );
                    }
                  break;
                }
              }
            }
            else{
              hInitFunction( hGroup[ nI ] );
            }
          }
        }
      }
      //we have the new implementation - css3 style selectors, so return
      return;
    }
  }
  hSelectorRegEx = /([a-z0-9_]*)([\.#@]?)([a-z0-9_=~]*)/i;
  hAttributeRegEx = /([a-z0-9_]*)([=~])?([a-z0-9_]*)/i;
  aSelectorData = hSelectorRegEx.exec( sSelector );
  if( aSelectorData[ 1 ] != '' ){
    hGroup  = hParent.getElementsByTagName( aSelectorData[ 1 ] );
    for( nI = 0; nI < hGroup.length; nI ++ ){
      hGroup[ nI ].markExt = true;
    }
    for( nI = 0; nI < hGroup.length; nI ++ ){
      if( !hGroup[ nI ].markExt ){
        continue;
      }
      else{
        hGroup[ nI ].markExt = false;
      }
        if( aSelectorData[ 2 ] != '' ){
          switch( aSelectorData[ 2 ] ){
          case '.':
            if( hGroup[ nI ].className == aSelectorData[ 3 ] ){
              hInitFunction( hGroup[ nI ] );
            }
          break;
          case '#' :
            if( hGroup[ nI ].id == aSelectorData[ 3 ] ){
              hInitFunction( hGroup[ nI ] );
            }
          break;
          case '@' :
            aAttributeData = hAttributeRegEx.exec( aSelectorData[ 3 ] );
            sAttribute = hGroup[ nI ].getAttribute( aAttributeData[ 1 ] );
            if(  sAttribute !== null && sAttribute.length > 0  ) {
              if( aAttributeData[ 3 ] != '' ){
                if( aAttributeData[ 2 ] == '=' ){
                  if( sAttribute == aAttributeData[ 3 ] ){
                    hInitFunction( hGroup[ nI ] );
                  }
                }
                else { /* the case is like ~ */
                  if( sAttribute.indexOf( aAttributeData[ 3 ] ) >= 0 ) {
                    hInitFunction( hGroup[ nI ] );
                  }
                }
              }
              else{
                hInitFunction( hGroup[ nI ] );
              }
            }
          break;
        }
      }
    }
  }
};

cDomExtensionManager.initialize = function(){
  var aSelectors;
  for( var nKey in cDomExtensionManager.aExtensions ){
    if(cDomExtensionManager.aExtensions.hasOwnProperty(nKey)){
      aSelectors = cDomExtensionManager.aExtensions[ nKey ].aSelectors;
      for(var nKey2 in aSelectors ){
        if(aSelectors.hasOwnProperty(nKey2)){
          cDomExtensionManager.initSelector( cDomExtensionManager.aExtensions[ nKey ].hParent, aSelectors[ nKey2 ], cDomExtensionManager.aExtensions[ nKey ].hInitFunction );
        }
      }
    }
  }
};

if( window.addEventListener ){
  window.addEventListener( 'load', cDomExtensionManager.initialize, false );
}
else if( window.attachEvent ){
  window.attachEvent( 'onload', cDomExtensionManager.initialize );
}

//   xmlextras.js
// used to find the Automation server name
function getDomDocumentPrefix() {
	if (getDomDocumentPrefix.prefix){
		return getDomDocumentPrefix.prefix;
	}

	var prefixes = ["MSXML2", "Microsoft", "MSXML", "MSXML3"];
	var o;
	for (var i = 0; i < prefixes.length; i++) {
		try {
			// try to create the objects
			o = new ActiveXObject(prefixes[i] + ".DomDocument");
			getDomDocumentPrefix.prefix = prefixes[i];
            return getDomDocumentPrefix.prefix;
		}
		catch(ex){}
	}
	throw new Error("Could not find an installed XML parser");
}

function getXmlHttpPrefix() {
	if (getXmlHttpPrefix.prefix){
		return getXmlHttpPrefix.prefix;
	}
	var prefixes = ["MSXML2", "Microsoft", "MSXML", "MSXML3"];
	var o;
	for (var i = 0; i < prefixes.length; i++) {
		try {
			// try to create the objects
			o = new ActiveXObject(prefixes[i] + ".XmlHttp");
			getXmlHttpPrefix.prefix = prefixes[i];
            return getXmlHttpPrefix.prefix;
		}
		catch (ex){}
	}

	throw new Error("Could not find an installed XML parser");
}

//////////////////////////
// Start the Real stuff //
//////////////////////////


// XmlHttp factory
function XmlHttp() {}

XmlHttp.create = function () {
	try {
		if (window.XMLHttpRequest) {
			var req = new XMLHttpRequest();

			// some versions of Moz do not support the readyState property
			// and the onreadystate event so we patch it!
			if (req.readyState === null) {
				req.readyState = 1;
				req.addEventListener("load", function () {
					req.readyState = 4;
					if (typeof req.onreadystatechange == "function"){
						req.onreadystatechange();
					}
				}, false);
			}

			return req;
		}
		if (window.ActiveXObject) {
			return new ActiveXObject(getXmlHttpPrefix() + ".XmlHttp");
		}
	}
	catch (ex) {}
	// fell through
	throw new Error("Your browser does not support XmlHttp objects");
};

// XmlDocument factory
function XmlDocument() {}

XmlDocument.create = function () {
	try {
		// DOM2
		if (document.implementation && document.implementation.createDocument) {
			var doc = document.implementation.createDocument("", "", null);

			// some versions of Moz do not support the readyState property
			// and the onreadystate event so we patch it!
			if (doc.readyState === null) {
				doc.readyState = 1;
				doc.addEventListener("load", function () {
					doc.readyState = 4;
					if (typeof doc.onreadystatechange == "function"){
						doc.onreadystatechange();
					}
				}, false);
			}

			return doc;
		}
		if (window.ActiveXObject){
			return new ActiveXObject(getDomDocumentPrefix() + ".DomDocument");
		}
	}
	catch (ex) {}
	throw new Error("Your browser does not support XmlDocument objects");
};

// Create the loadXML method and xml getter for Mozilla
if (window.DOMParser &&
	window.XMLSerializer &&
	window.Node && window.Node.prototype && window.Node.prototype.__defineGetter__) {

	// XMLDocument did not extend the Document interface in some versions
	// of Mozilla. Extend both!
	//XMLDocument.prototype.loadXML =
	Document.prototype.loadXML = function (s) {

		// parse the string to a new doc
		var doc2 = (new DOMParser()).parseFromString(s, "text/xml");

		// remove all initial children
		while (this.hasChildNodes()){
			this.removeChild(this.lastChild);
		}

		// insert and import nodes
		for (var i = 0; i < doc2.childNodes.length; i++) {
			this.appendChild(this.importNode(doc2.childNodes[i], true));
		}
	};


	/*
	 * xml getter
	 *
	 * This serializes the DOM tree to an XML String
	 *
	 * Usage: var sXml = oNode.xml
	 *
	 */
	// XMLDocument did not extend the Document interface in some versions
	// of Mozilla. Extend both!
	/*
	XMLDocument.prototype.__defineGetter__("xml", function () {
		return (new XMLSerializer()).serializeToString(this);
	});
	*/
	Document.prototype.__defineGetter__("xml", function () {
		return (new XMLSerializer()).serializeToString(this);
	});
}

//   modomt.js
if ( document.ELEMENT_NODE === null ){
  document.ELEMENT_NODE = 1;
  document.TEXT_NODE = 3;
}

function getSubNodeByName( hNode, sNodeName ){
  if( hNode !== null ){
    var nC	= 0;
    var hNodeChildren = hNode.childNodes;
    var hCNode = null;
    while( nC < hNodeChildren.length ){
      hCNode = hNodeChildren.item( nC++ );
      if( ( hCNode.nodeType == 1 ) && ( hCNode.nodeName.toLowerCase() == sNodeName ) ){
        return hCNode;
      }
    }
  }
  return null;
}

function getPrevNodeSibling( hNode ){
  if( hNode !== null ){
    do{
      hNode = hNode.previousSibling;
    }
    while(hNode !== null && hNode.nodeType != 1);
    return hNode;
  }
}

function getNextNodeSibling( hNode ){
  if( hNode !== null ){
    do {
      hNode = hNode.nextSibling;
    }
    while( hNode !== null && hNode.nodeType != 1 );
    return hNode;
  }
}

function getLastSubNodeByName( hNode, sNodeName ){
  if( hNode !== null )	{
    var hNodeChildren = hNode.childNodes;
    var hCNode = null;
    var nLength = hNodeChildren.length - 1;
    while( nLength >=0  ){
      hCNode = hNodeChildren.item( nLength );
      if( ( hCNode.nodeType == 1 ) && ( hCNode.nodeName.toLowerCase() == sNodeName ) ){
        return hCNode;
      }
      nLength--;
    }
  }
  return null;
}

function getSubNodeByProperty( hNode, sProperty, sPropValue ){
  if( hNode !== null ){
    var nC	= 0;
    var hNodeChildren = hNode.childNodes;
    var hCNode = null;
    var hProp;
    sPropValue = sPropValue.toLowerCase();
    while( nC < hNodeChildren.length ){
      hCNode = hNodeChildren.item( nC++ );
      if( hCNode.nodeType == document.ELEMENT_NODE ){
        hProp = eval( 'hCNode.'+sProperty );
        if( typeof( sPropValue ) != 'undefined' ){
          if( hProp.toLowerCase() == sPropValue ){
            return hCNode;
          }
        }
        else{
          return hCNode;
        }
      }
    }
  }
  return null;
}

function findAttribute( hNode, sAtt ){
  sAtt = sAtt.toLowerCase();
  for( var nI = 0; nI < hNode.attributes.length; nI++ ){
    if( hNode.attributes.item( nI ).nodeName.toLowerCase() == sAtt ){
      return hNode.attributes.item( nI ).nodeValue;
    }
  }
  return null;
}

function getSubNodeByAttribute( hNode, sAtt, sAttValue ){
  if( hNode !== null ){
    var nNc = 0;
    var nC	= 0;
    var hNodeChildren = hNode.childNodes;
    var hCNode = null;
    var sAttribute;
    sAttValue = sAttValue.toLowerCase();
    while( nC < hNodeChildren.length ){
      hCNode = hNodeChildren.item( nC++ );
      if( hCNode.nodeType == document.ELEMENT_NODE ){
        sAttribute = hCNode.getAttribute( sAtt );
        if( sAttribute && sAttribute.toLowerCase() == sAttValue ){
          return hCNode;
        }
      }
      nNc++;
    }
  }
  return null;
}

function getLastSubNodeByAttribute( hNode, sAtt, sAttValue ){
  if( hNode !== null ){
    var hNodeChildren = hNode.childNodes;
    var hCNode = null;
    var nLength = hNodeChildren.length - 1;
    while( nLength >= 0 ){
      hCNode = hNodeChildren.item( nLength );
      if( hCNode.nodeType == document.ELEMENT_NODE ){
        var sAttribute = hCNode.getAttribute( sAtt );
        if( sAttribute && sAttribute.toLowerCase() == sAttValue ){
          return hCNode;
        }
      }
      nLength--;
    }
  }
  return null;
}

function getParentByTagName( hNode, sParentTagName ){
  while( ( hNode.tagName ) && !( /(body|html)/i.test( hNode.tagName ) ) ){
    if( hNode.tagName == sParentTagName ){
      return hNode;
    }
    hNode = hNode.parentNode;
  }
  return null;
}

function getParentByAttribute( hNode, sAtt, sAttValue ){
  while( ( hNode.tagName ) && !( /(body|html)/i.test( hNode.tagName ) ) ){
    //opera strangely returns non null result sometimes
    var sAttr = hNode.getAttribute( sAtt );
    if( sAttr !== null && sAttr.toString().length > 0 ){
      if( sAttValue !== null ){
        if( sAttr == sAttValue ){
          return hNode;
        }
      }
      else{
        return hNode;
      }
    }
    hNode = hNode.parentNode;
  }
  return null;
}

function getParentByProperty( hNode, sProperty, sPropValue ){
  while( ( hNode.tagName ) && !( /(body|html)/i.test( hNode.tagName ) ) ){
    //opera strangely returns non null result sometimes
    var hProp = eval( 'hNode.'+sProperty );
    if( hProp !== null && hProp.toString().length > 0 ){
      if( sPropValue !== null ){
        if( hProp == sPropValue ){
          return hNode;
        }
      }
      else{
        return hNode;
      }
    }
    hNode = hNode.parentNode;
  }
  return null;
}


function getNodeText( hNode ){
  var sRes;
  if( hNode === null ){
    return '';
  }
  if( hNode.hasChildNodes() ){
    sRes = hNode.childNodes.item(0).nodeValue;
  }
  else{
    sRes = hNode.text;
  }
  return sRes;
}

//   morss.js
function cRSSParser( source ){
  this.sName =	cRSSParser.CS_NAME + cRSSParser.CN_COUNT++;
  this.hObj =		this.sName;
  eval( this.hObj + ' = this' );
  this.init( source );
}

cRSSParser.CS_NAME = "cRSSParser";
cRSSParser.CN_COUNT = 0;
cRSSParser.CN_TIMEOUT = 60;

cRSSParser.attachTo = function( source ){
  var tmp = new cRSSParser( source );
};

cRSSParser.prototype.init = function( source ){
  this.source = source;
  this.source.rssReader = this;
  this.onRssLoad = function(){};
  var sOnLoad = this.source.getAttribute( 'onrssload' );
  if( sOnLoad !== null && sOnLoad.length > 0 ){
    this.onRssLoad = new Function( sOnLoad );
  }
  this.hXMLHttp = XmlHttp.create();
  this.load();
};

cRSSParser.prototype.load = function() {
  var hNewContainer = document.createElement( 'div' );
  hNewContainer.className = 'wait';
  this.source.appendChild( hNewContainer );
  var sAddress = "/img/rss_proxy/?url="+this.source.getAttribute( 'href' )+"&timeout="+cRSSParser.CN_TIMEOUT;
  this.hXMLHttp.open( 'GET', sAddress, true );
  this.hXMLHttp.onreadystatechange = new Function( 'var sObjectName = "'+this.sName+'"; cRSSParser.readyStateChange( eval( sObjectName ) )' );
  this.hXMLHttp.send( null );
  this.hAbortTimeout = setTimeout( this.hObj+'.onTimeoutError()', cRSSParser.CN_TIMEOUT*1000 );
};

cRSSParser.readyStateChange = function( hThis ){
  if( hThis.hXMLHttp.readyState == 4 ){
    var hError = hThis.hXMLHttp.parseError;
    if( typeof hError == 'undefined' ){
      hError = {};
      hError.errorCode = 0;
      hError.reason = '';
    }
    if ( !hThis.hXMLHttp.responseXML.documentElement || hThis.hXMLHttp.responseXML.documentElement.tagName == 'parsererror' ){
      hError.errorCode = 1 ;
      hError.reason = "Error";
    }
    if( hError && hError.errorCode !== 0 ){
      //alert( hError.reason );
    }
    else{
      clearTimeout( hThis.hAbortTimeout);
      var hWaitContainer = getSubNodeByName( hThis.source, 'div' );
      var hash_arr = hThis.source.getAttribute('href').split('#');
      hash_arr = (hash_arr.length==2 ? hash_arr[1] : '1|0').split('|');
      var limit =       hash_arr[0];
      var showTitle =   hash_arr[1]==1;

      var hContainer = hThis.createHTML( hThis.hXMLHttp.responseXML,limit,showTitle);
      hThis.source.replaceChild( hContainer, hWaitContainer );
      hThis.source.setAttribute( 'href', '#' ); // make whole zone no longer clickable
      hThis.onRssLoad();
    }
  }
};

cRSSParser.prototype.createHTML = function( hXML, limit, showTitle){
  var hContainer, hItemList;
  var hNode = getSubNodeByName( hXML.documentElement, 'channel' );
  var hChildren = hNode.getElementsByTagName( 'item' );
  if( hChildren.length === 0 ){
    hChildren = hXML.documentElement.getElementsByTagName( 'item' );
  }
  if (hChildren.length===0){
    hContainer = document.createElement( 'div' );
    hItemList = document.createElement( 'ul' );
    var hItemNode = document.createElement( 'li' );
    var hItemDiv =  document.createElement( 'a' );
    var hTextNode = document.createTextNode( 'No Results' );
    hItemDiv.appendChild( hTextNode );
    hItemNode.appendChild( hItemDiv );
    hItemList.appendChild( hItemNode );
    hContainer.appendChild( hItemList );
    return hContainer;
  }
  var count = (hChildren.length>limit ? limit : hChildren.length);
  var sLink = "";
  var sTitle = "";
  sTitle = getNodeText( getSubNodeByName( hNode, 'title' ) );
  sLink = getNodeText( getSubNodeByName( hNode, 'link' ) );
  hContainer = document.createElement( 'div' );
  var hLink = document.createElement( 'a' );
  if (showTitle) {
    hLink.href = sLink;
    hLink.rel = 'external';
    hLink.appendChild( document.createTextNode( sTitle ) );
    hContainer.appendChild( hLink );
  }
  hItemList = document.createElement( 'ul' );
  hContainer.appendChild( hItemList );
  for ( var i = 0; i < count; i++ ) {
    var hItem = hChildren.item(i);
    if( hItem.nodeType == 1 ){
      if( hItem.nodeName == "item" ){
        hItemList.appendChild( this.processItem( hItem ) );
      }
    }
  }
  return hContainer;
};

cRSSParser.prototype.onTimeoutError = function(){
  this.hXMLHttp.abort();
  var hContainer = document.createElement( 'div' );
  hContainer.className = 'error';
  var hErrorNode = document.createTextNode( 'Error loading: '+this.source.getAttribute( 'href' ));
  hContainer.appendChild( hErrorNode );
  var hWaitContainer = getSubNodeByName( this.source, 'div' );
  this.source.replaceChild( hContainer, hWaitContainer );
};

cRSSParser.prototype.processItem = function( sItemXML ){
  var hItemNode = document.createElement( 'li' );
  var hItemLink = document.createElement( 'a' );
  hItemLink.href = getNodeText( getSubNodeByName( sItemXML, 'link' ) );
  hItemLink.rel = 'external';
  hItemLink.appendChild( document.createTextNode( getNodeText( getSubNodeByName( sItemXML, 'title' ) ) ) );
  hItemNode.appendChild( hItemLink );
  hItemNode.title = getNodeText( getSubNodeByName( sItemXML, 'description' ) ).replace(/(<([^>]+)>)/ig,'').replace(/^\s*/,'').replace(/\s*$/,'').replace('&amp;','&').replace('&nbsp;',' ').substr(0,250);
  return hItemNode;
};