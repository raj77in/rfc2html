<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


    <xsl:template match="/rfc-index">
        <h2>RFC Index</h2>
        <table border="1" class="Table" id="Table">
            <thead>
                <th>RFC Number</th>
                <th>Description</th>
            </thead>
            <xsl:for-each select="rfc-entry">
                <tr>
                    <xsl:variable name="rfcno" select="substring-after(doc-id,'RFC')"/>
                    <td><a href="/repo/RFC/rfc2html/rfc2html.php?in={$rfcno}"> RFC<xsl:value-of select="$rfcno"/></a> </td>
                    <td><xsl:value-of select="title"/></td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>


</xsl:stylesheet>
