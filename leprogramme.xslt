<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="text" encoding="UTF-8" />
  <xsl:strip-space elements="*"/>
  <xsl:template match="/xjx/cmd[@t='leprogramme']">
&lt;!DOCTYPE html [
&lt;!ENTITY nbsp "&amp;#160;"&gt;
&lt;!ENTITY eacute "é"&gt;
]&gt;
&lt;!-- http://o.mengue.free.fr/blog/2008/02/11/51-les-programmes-tele-de-telerama-en-xmltv --&gt;
&lt;html&gt;&lt;body&gt;<xsl:value-of select="text()"/>&lt;/body&gt;&lt;/html&gt;
  </xsl:template>
  <xsl:template match="/xjx/cmd[@n!='as']"/>
</xsl:stylesheet>
