SELECT
   f.title,
   SUM(sf.impressions) AS impressions,
   SUM(sf.clicks) AS clicks,
   SUM(sf.clicks) * 100 / SUM(sf.impressions) AS ctr,
   SUM(sf.earned_admin) / SUM(sf.clicks) AS cpc,
   SUM(sf.earned_admin) AS earned
FROM
   feeds f
      INNER JOIN stat_feeds sf ON (f.id_feed = sf.id_feed)
WHERE
   sf.stat_date BETWEEN '<%PSTART%>' AND '<%PEND%>'
   <%EXS%>AND f.id_feed IN (<%EXTRA%>)<%EXE%>
GROUP BY
   f.id_feed
