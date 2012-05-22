SELECT
   UNIX_TIMESTAMP(ssc.stat_date) AS stat_date,
   s.url,
   c.name,
   SUM(ssc.impressions) AS impressions,
   SUM(ssc.clicks) AS clicks,
   SUM(ssc.clicks) * 100 / SUM(ssc.impressions) AS ctr,
   SUM(ssc.earned_admin) AS earned
FROM
   stat_sites_channels ssc
      INNER JOIN sites s ON (ssc.id_site = s.id_site)
      INNER JOIN channels c ON (ssc.id_channel = c.id_channel)
WHERE
   ssc.stat_date BETWEEN '<%PSTART%>' AND '<%PEND%>'
GROUP BY
   ssc.stat_date,
   s.url,
   c.id_channel
ORDER BY
   ssc.stat_date
