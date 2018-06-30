<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ecc_detail="http://www.ecclesiact.com/?page=help_rss_ns">
<xsl:output method="html" />
<xsl:variable name="title" select="/rss/channel/title"/>
<xsl:variable name="URL" select="/rss/channel/link"/>

<xsl:template match="/">
<html>
<head>
<title><xsl:value-of select="$title"/></title>
<link rel="stylesheet" href="./?mode=xslcss" type="text/css"/>
</head>
<xsl:apply-templates select="rss/channel"/>
</html>
</xsl:template>
<xsl:template match="channel">
<body>
<div class="topbox">
  <div class="box">
    <h2><xsl:value-of select="$title"/></h2>
    <xsl:if test="contains($URL,'submode=events')">
    <div class="fltclear">Below are the future events available from this channel:
      <div class="paditembox">
        <xsl:apply-templates select="item"/>
      </div>
    </div>
    </xsl:if>
    <xsl:if test="contains($URL,'submode=news')">
    <div class="fltclear">Below are the latest news items available from this channel (most recent shown first):
      <div class="paditembox">
        <xsl:apply-templates select="item"/>
      </div>
    </div>
    </xsl:if>
  </div>
   <div class="box">
    <h2>RSS Feeds available from this site:</h2>
      <ul>
        <li><a href="./?mode=rss&amp;submode=events" class="item"><img height="15" hspace="5" vspace="0" border="0" width="32" alt="RSS" src="./img/?mode=sysimg&amp;img=icon_rss.gif" title="RSS" align="left" /> Events</a></li>
        <li><a href="./?mode=rss&amp;submode=news" class="item"><img height="15" hspace="5" vspace="0" border="0" width="32" alt="RSS" src="./img/?mode=sysimg&amp;img=icon_rss.gif" title="RSS" align="left" /> News</a></li>
      </ul>
    </div>
  </div>

  <div class="box">
  <h2>What is this page?</h2>
  <p>This is an RSS feed. RSS feeds allow you to stay up to date with the latest news and events we publish on our web site.</p>
  <p>To subscribe to it, you will need a News Reader or other similar device.
  </p>
  <p>You can subscribe to this RSS feed in a number of ways, including the following:</p>
  <ul>
    <li>Drag the orange RSS button into your News Reader</li>
    <li>Drag the URL of the RSS feed into your News Reader</li>
    <li>Cut and paste the URL of the RSS feed into your News Reader</li>
  </ul>
  <p><a href="http://www.feedvalidator.org/check.cgi?url={link}" class="item"><img src="./img/?mode=sysimg&amp;img=valid-rss.png" alt="[Valid RSS]" title="Validate this RSS feed" border='0'/></a> Click to validate this feed</p>
</div>

</body>
</xsl:template>
		
<xsl:template match="item">
  <div id="item">
    <ul>
      <li>
        <a href="{link}" class="item">
          <xsl:value-of select="ecc_detail:title"/>
        </a> <small> Date: <xsl:value-of select="ecc_detail:date" /></small><br/>
        <div>
          <xsl:value-of select="description" />
        </div>
        <div><small>(Pub: <xsl:value-of select="pubDate" />)</small></div>
      </li>
    </ul>
  </div>
</xsl:template>
	
</xsl:stylesheet>
