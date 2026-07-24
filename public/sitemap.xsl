<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xhtml="http://www.w3.org/1999/xhtml"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="en">
      <head>
        <title>XML Sitemap | PolyCMS</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style type="text/css">
          body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 2rem 1rem;
            line-height: 1.5;
          }
          .container {
            max-width: 1200px;
            margin: 0 auto;
          }
          .header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 12px;
            color: #ffffff;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
          }
          .header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
          }
          .header h1 span {
            color: #38bdf8;
            margin-left: 0.5rem;
          }
          .header p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.95rem;
          }
          .header p a {
            color: #38bdf8;
            text-decoration: none;
            transition: color 0.15s ease;
          }
          .header p a:hover {
            color: #7dd3fc;
            text-decoration: underline;
          }
          .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
          }
          .stat-card {
            background-color: #ffffff;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
          }
          .stat-card .label {
            font-size: 0.875rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
          }
          .stat-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0.25rem;
          }
          .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
          }
          th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
          }
          td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            word-break: break-all;
          }
          tr:last-child td {
            border-bottom: none;
          }
          tr:hover td {
            background-color: #f8fafc;
          }
          .url-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
          }
          .url-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
          }
          .badge-lang {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #e0f2fe;
            color: #0369a1;
            text-decoration: none;
            margin-right: 0.375rem;
            margin-bottom: 0.25rem;
            border: 1px solid #bae6fd;
            transition: all 0.15s ease;
          }
          .badge-lang:hover {
            background-color: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
          }
          .badge-lang.current {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
            cursor: default;
          }
          .badge-lang.current:hover {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
          }
          .text-muted {
            color: #64748b;
          }
          .priority-bar-wrapper {
            background-color: #e2e8f0;
            border-radius: 9999px;
            width: 80px;
            height: 6px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 0.5rem;
          }
          .priority-bar {
            height: 100%;
            border-radius: 9999px;
          }
          .priority-text {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
            display: inline-block;
            vertical-align: middle;
          }
          .footer {
            text-align: center;
            margin-top: 3rem;
            font-size: 0.8rem;
            color: #94a3b8;
          }
          .footer a {
            color: #64748b;
            text-decoration: none;
          }
          .footer a:hover {
            text-decoration: underline;
          }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1>PolyCMS<span>XML Sitemap</span></h1>
            <p>Generated by the <a href="https://polycms.org" target="_blank">PolyCMS</a> MTOptimize SEO engine. This is a machine-readable XML Sitemap intended for search engine crawlers like Google, Bing, and Yandex.</p>
          </div>

          <!-- Determine if it is a Sitemap Index or URL Set -->
          <xsl:choose>
            <xsl:when test="sitemap:sitemapindex">
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="label">Sitemap Type</div>
                  <div class="value">Index</div>
                </div>
                <div class="stat-card">
                  <div class="label">Total Sitemaps</div>
                  <div class="value"><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></div>
                </div>
              </div>

              <div class="card">
                <table>
                  <thead>
                    <tr>
                      <th style="width: 70%;">Sitemap URL</th>
                      <th style="width: 30%;">Last Modified</th>
                    </tr>
                  </thead>
                  <tbody>
                    <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                      <xsl:variable name="sitemap_loc" select="sitemap:loc"/>
                      <tr>
                        <td>
                          <a href="{$sitemap_loc}" class="url-link"><xsl:value-of select="sitemap:loc"/></a>
                        </td>
                        <td>
                          <span class="text-muted"><xsl:value-of select="sitemap:lastmod"/></span>
                        </td>
                      </tr>
                    </xsl:for-each>
                  </tbody>
                </table>
              </div>
            </xsl:when>

            <xsl:otherwise>
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="label">Sitemap Type</div>
                  <div class="value">URL Set</div>
                </div>
                <div class="stat-card">
                  <div class="label">Total URLs</div>
                  <div class="value"><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></div>
                </div>
              </div>

              <div class="card">
                <table>
                  <thead>
                    <tr>
                      <th style="width: 40%;">URL</th>
                      <th style="width: 10%; text-align: center;">Images</th>
                      <th style="width: 15%;">Priority</th>
                      <th style="width: 15%;">Change Freq</th>
                      <th style="width: 20%;">Language Alternates</th>
                    </tr>
                  </thead>
                  <tbody>
                    <xsl:for-each select="sitemap:urlset/sitemap:url">
                      <xsl:variable name="url_loc" select="sitemap:loc"/>
                      <tr>
                        <td>
                          <a href="{$url_loc}" class="url-link"><xsl:value-of select="sitemap:loc"/></a>
                          <xsl:if test="sitemap:lastmod">
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">
                              Last modified: <xsl:value-of select="sitemap:lastmod"/>
                            </div>
                          </xsl:if>
                        </td>
                        <td style="text-align: center;">
                          <xsl:choose>
                            <xsl:when test="count(image:image) &gt; 0">
                              <span class="text-muted"><xsl:value-of select="count(image:image)"/></span>
                            </xsl:when>
                            <xsl:otherwise>
                              <span class="text-muted">-</span>
                            </xsl:otherwise>
                          </xsl:choose>
                        </td>
                        <td>
                          <xsl:choose>
                            <xsl:when test="sitemap:priority">
                              <xsl:variable name="p_val" select="number(sitemap:priority)"/>
                              <xsl:variable name="p_color">
                                <xsl:choose>
                                  <xsl:when test="$p_val &gt;= 0.8">#10b981</xsl:when>
                                  <xsl:when test="$p_val &gt;= 0.5">#3b82f6</xsl:when>
                                  <xsl:otherwise>#94a3b8</xsl:otherwise>
                                </xsl:choose>
                              </xsl:variable>
                              <div class="priority-bar-wrapper">
                                <div class="priority-bar" style="width: {$p_val * 100}%; background-color: {$p_color};"></div>
                              </div>
                              <span class="priority-text"><xsl:value-of select="sitemap:priority"/></span>
                            </xsl:when>
                            <xsl:otherwise>
                              <span class="text-muted">-</span>
                            </xsl:otherwise>
                          </xsl:choose>
                        </td>
                        <td>
                          <xsl:choose>
                            <xsl:when test="sitemap:changefreq">
                              <span style="text-transform: capitalize;"><xsl:value-of select="sitemap:changefreq"/></span>
                            </xsl:when>
                            <xsl:otherwise>
                              <span class="text-muted">-</span>
                            </xsl:otherwise>
                          </xsl:choose>
                        </td>
                        <td>
                          <xsl:for-each select="xhtml:link[@rel='alternate']">
                            <xsl:variable name="alt_href" select="@href"/>
                            <xsl:variable name="alt_lang" select="@hreflang"/>
                            <xsl:choose>
                              <xsl:when test="$alt_href = $url_loc">
                                <span class="badge-lang current">
                                  <xsl:value-of select="$alt_lang"/>
                                </span>
                              </xsl:when>
                              <xsl:otherwise>
                                <a href="{$alt_href}" class="badge-lang">
                                  <xsl:value-of select="$alt_lang"/>
                                </a>
                              </xsl:otherwise>
                            </xsl:choose>
                          </xsl:for-each>
                        </td>
                      </tr>
                    </xsl:for-each>
                  </tbody>
                </table>
              </div>
            </xsl:otherwise>
          </xsl:choose>

          <div class="footer">
            <p>Generated by <a href="https://polycms.org" target="_blank">PolyCMS MTOptimize SEO Engine</a>. All times are in UTC.</p>
          </div>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
