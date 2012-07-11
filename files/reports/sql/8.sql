SELECT
   c.name AS camp,
   g.name AS grp,
   at.name,
   SUM(sa.spent) AS spent,
   SUM(sa.impressions) AS impressions,
   SUM(sa.clicks), SUM(sa.clicks) * 100 / SUM(sa.impressions) AS ctr
FROM
   stat_ads sa
      INNER JOIN ads ad ON (sa.id_ad = ad.id_ad)
      INNER JOIN groups g ON (ad.id_group = g.id_group)
      INNER JOIN campaigns c ON (g.id_campaign = c.id_campaign)
      INNER JOIN advertisers a ON (c.id_entity_advertiser = a.id_entity_advertiser)
      INNER JOIN ad_types at ON (at.id_ad_type = ad.id_ad_type)
WHERE
   sa.stat_date BETWEEN '<%PSTART%>' AND '<%PEND%>' AND
   a.id_entity_advertiser = <%ID_ENTITY%>
GROUP BY
   g.id_group,
   at.id_ad_type
