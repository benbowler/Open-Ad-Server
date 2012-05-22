(
   SELECT
      UNIX_TIMESTAMP('<%PSTART%>') AS fdate,
      'start balance',
      0,
      IF (mf.id_entity_receipt = <%ID_ENTITY%>, mf.balance_receipt - mf.value, mf.balance_expense + mf.value) AS bal
   FROM
      money_flows mf
   WHERE 
      mf.flow_date BETWEEN '<%PSTART%>' AND '<%PEND%>' + INTERVAL 1 DAY AND
      (
         mf.id_entity_receipt = <%ID_ENTITY%> OR
         mf.id_entity_expense = <%ID_ENTITY%>
      )
   ORDER BY
      mf.flow_date
   LIMIT
      1
) UNION ALL (
   SELECT
      UNIX_TIMESTAMP(mf.flow_date),
      mf.flow_program,
      mf.value,
      0
   FROM
      money_flows mf
   WHERE
      mf.flow_date BETWEEN '<%PSTART%>' AND '<%PEND%>' + INTERVAL 1 DAY AND
      mf.flow_program IN ('deposit', 'withdraw', 'move', 'deduction') AND
      (
         mf.id_entity_receipt = <%ID_ENTITY%> OR
         mf.id_entity_expense = <%ID_ENTITY%>
      )
) UNION ALL (
   SELECT
      UNIX_TIMESTAMP(LEAST(DATE(LAST_DAY(mf.flow_date)), '<%PEND%>')) + 60*60*24 - 2,
      CONCAT('{@charged by month@} {@', DATE_FORMAT(mf.flow_date, '%M'), '@}'),
      -SUM(value),
      0
   FROM
      money_flows mf
   WHERE
      mf.flow_date BETWEEN '<%PSTART%>' AND '<%PEND%>' + INTERVAL 1 DAY AND
      mf.flow_program IN ('click', 'program') AND
      mf.id_entity_expense = <%ID_ENTITY%>
   GROUP BY
      LAST_DAY(mf.flow_date)
) UNION ALL (
   SELECT
      UNIX_TIMESTAMP('<%PEND%>') + 60*60*24 - 1,
      'end balance',
      0,
      IF(mf.id_entity_receipt = <%ID_ENTITY%>, mf.balance_receipt, mf.balance_expense)
   FROM
      money_flows mf
   WHERE
      mf.flow_date BETWEEN '<%PSTART%>' AND '<%PEND%>' + INTERVAL 1 DAY AND
      (
         mf.id_entity_receipt = <%ID_ENTITY%> OR
         mf.id_entity_expense = <%ID_ENTITY%>
      )
   ORDER BY
      mf.flow_date DESC
   LIMIT
      1
)
ORDER BY
   fdate
