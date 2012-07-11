SELECT
   e.e_mail,
   SUM(sa.impressions) AS impressions,
   SUM(sa.clicks) AS clicks,
   SUM(sa.clicks) * 100 / SUM(sa.impressions) AS ctr,
   SUM(sa.spent) AS earned
FROM
   advertisers a
      INNER JOIN entities e ON (a.id_entity_advertiser = e.id_entity)
      INNER JOIN stat_advertisers sa ON (a.id_entity_advertiser = sa.id_entity_advertiser)
WHERE
   sa.stat_date BETWEEN '<%PSTART%>' AND '<%PEND%>'
   <%EXS%>AND a.id_entity_advertiser IN (<%EXTRA%>)<%EXE%>
GROUP BY
   a.id_entity_advertiser
