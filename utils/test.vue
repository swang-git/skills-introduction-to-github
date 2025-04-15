<template>
<div>
</div>
</template>
<script setup>
function groupScenario (gx) {
  gsx.value = gx
  console.log(`groupScenario gsx=${gsx.value}`, grouped.value)
  const groupedPlayers = grpScenario(gsx.value, getTplayers())
  groupedPlayers.forEach((players, i) => {
    players.sort((a, b) => a.team < b.team ? -1 : 1)
    // console.table(players)
    const g = { players: players }
    grouped.value[i] = g
  })
  calcTeamHandicapDiffs()
}
function calcTeamHandicapDiffs () {
  const a = 'abcd'
  const a = 'abcd'
  const a = 'abcd'
  const a = 'abcd'
}
</script>