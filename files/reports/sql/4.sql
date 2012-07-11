(
   SELECT
      e.e_mail,
      UNIX_TIMESTAMP('<%PSTART_TIME%>') AS fdate,
      'start balance',
      0,
      IF (mf.id_entity_receipt = e.id_entity, mf.balance_receipt - mf.value, mf.balance_expense + mf.value) AS bal
   FROM
      entities e
         INNER JOIN advertisers a ON (a.id_entity_advertiser = e.id_entity)
         INNER JOIN money_flows mf ON (e.id_entity = mf.id_entity_receipt OR e.id_entity = mf.id_entity_expense)
   WHERE 
      mf.flow_date BETWEEN '<%PSTART_TIME%>' AND '<%PEND_TIME%>'
      <%EXS%>AND e.id_entity IN (<%EXTRA%>)<%EXE%>
   GROUP BY
      e.id_entity
) UNION ALL (
   SELECT
      e.e_mail,
      UNIX_TIMESTAMP(mf.flow_date),
      mf.flow_program,
      mf.value,
      0
   FROM
      entities e
         INNER JOIN advertisers a ON (a.id_entity_advertiser = e.id_entity)
         INNER JOIN money_flows mf ON (e.id_entity = mf.id_entity_receipt OR e.id_entity = mf.id_entity_expense)
   WHERE
      mf.flow_date BETWEEN '<%PSTART_TIME%>' AND '<%PEND_TIME%>' AND
      mf.flow_program IN ('deposit', 'withdraw', 'move', 'deduction')
      <%EXS%>AND e.id_entity IN (<%EXTRA%>)<%EXE%>
) UNION ALL (
   SELECT
      e.e_mail,
      UNIX_TIMESTAMP(LEAST(DATE(LAST_DAY(mf.flow_date)), '<%PEND_TIME%>')) + 60*60*24 - 2,
      CONCAT('{@charged by month@} {@', DATE_FORMAT(mf.flow_date, '%M'), '@}'),
      -SUM(value),
      0
   FROM
      entities e
         INNER JOIN advertisers a ON (a.id_entity_advertiser = e.id_entity)
         INNER JOIN money_flows mf ON (e.id_entity = mf.id_entity_expense)
   WHERE
      mf.flow_date BETWEEN '<%PSTART_TIME%>' AND '<%PEND_TIME%>' AND
      mf.flow_program IN ('click', 'program')
      <%EXS%>AND e.id_entity IN (<%EXTRA%>)<%EXE%>
   GROUP BY
      e.id_entity,
      LAST_DAY(mf.flow_date)
) UNION ALL (
   SELECT
      e.e_mail,
      UNIX_TIMESTAMP('<%PEND_TIME%>') + 60*60*24 - 1,
      'end balance',
      0,
      IF(mf.id_entity_receipt = e.id_entity, mf.balance_receipt, mf.balance_expense)
   FROM
      entities e
         INNER JOIN advertisers a ON (a.id_entity_advertiser = e.id_entity)
         INNER JOIN money_flows mf ON (e.id_entity = mf.id_entity_receipt OR e.id_entity = mf.id_entity_expense)
   WHERE
      mf.flow_date BETWEEN '<%PSTART_TIME%>' AND '<%PEND_TIME%>'
      <%EXS%>AND e.id_entity IN (<%EXTRA%>)<%EXE%>
   GROUP BY
      e.id_entity
   ORDER BY
      mf.flow_date DESC
)
ORDER BY
   e_mail,
   fdate
