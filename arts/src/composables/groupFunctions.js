import { Partition } from './partitionClass.js'
export function groupFunctions() {
  const getABS = (hcap) => { return  hcap==null ? null : Math.abs(hcap) <= 0.0001 ? 0 : Math.abs(hcap).toFixed(2) }
  const grpScenario = (gsx, players) => {
    const tplayers = players.sort((a, b) => parseFloat(a.handicap) < parseFloat(b.handicap) ? -1 : 1)
    if      (gsx === 1) return grpScenario1(tplayers)
    else if (gsx === 2) return grpScenario2(tplayers)
    else if (gsx === 3) return grpScenario3(tplayers)
    else if (gsx === 4) return grpScenario4(tplayers)
    else if (gsx === 5) return grpScenario5(tplayers)
    else if (gsx === 6) return grpScenario6(tplayers)
    else if (gsx === 9) return grpScenario9(tplayers)
    // calcGroupHandicapDiffs(tplayers, cap)
  }
  const getHcap = (a, b) => { return (parseFloat(a.handicap) + parseFloat(b.handicap)) / 2.0 }
  const grpScenario1 = (tplayers) => {
    console.log(`-fn-grpScenario1`, tplayers.map(p => p.handicap))
    const ngroups = tplayers.length / 4
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      const g = tplayers.slice(i*4, (i + 1)*4)
      // console.table(g)
      const p1 = g[0]; p1.team = 'A1'; p1.grp = i + 1;
      const p2 = g[3]; p2.team = 'A2'; p2.grp = i + 1;
      const p3 = g[1]; p3.team = 'B1'; p3.grp = i + 1;
      const p4 = g[2]; p4.team = 'B2'; p4.grp = i + 1;
      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      p1.hcap = ahcap
      p2.hcap = ahcap - bhcap
      p4.hcap = bhcap
      playersArray.push(g)
    }
    return playersArray
  }
  const grpScenario2 = (tplayers) =>{
    const ngroups = tplayers.length / 4
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      const g = tplayers.slice(i*4, (i + 1)*4)
      // console.table(g)
      if (i % 2 === 0) {
        const p1 = g[0]; p1.team = 'A1'; p1.grp = i + 1;
        const p2 = g[3]; p2.team = 'A2'; p2.grp = i + 1;
        const p3 = g[1]; p3.team = 'B1'; p3.grp = i + 1;
        const p4 = g[2]; p4.team = 'B2'; p4.grp = i + 1;
        const ahcap = getHcap(p1, p2)
        const bhcap = getHcap(p3, p4)
        p1.hcap = ahcap
        p2.hcap = ahcap - bhcap
        p4.hcap = bhcap
        playersArray.push(g)
      } else {
        const p1 = g[1]; p1.team = 'A1'; p1.grp = i + 1;
        const p2 = g[2]; p2.team = 'A2'; p2.grp = i + 1;
        const p3 = g[0]; p3.team = 'B1'; p3.grp = i + 1;
        const p4 = g[3]; p4.team = 'B2'; p4.grp = i + 1;
        const ahcap = getHcap(p1, p2)
        const bhcap = getHcap(p3, p4)
        p1.hcap = ahcap
        p2.hcap = ahcap - bhcap
        p4.hcap = bhcap
        playersArray.push(g)
      }
    }
    return playersArray
  }
  const grpScenario3 = (tplayers) => {
    const ngroups = tplayers.length / 4
    const end = tplayers.length - 1
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      const p1 = tplayers[i*2+0];         p1.team = 'A1'; p1.grp = i + 1
      const p2 = tplayers[end - i*2];     p2.team = 'A2'; p2.grp = i + 1
      const p3 = tplayers[i*2+1];         p3.team = 'B1'; p3.grp = i + 1
      const p4 = tplayers[end - i*2 - 1]; p4.team = 'B2'; p4.grp = i + 1
      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      p1.hcap = ahcap
      p2.hcap = ahcap - bhcap
      p4.hcap = bhcap
      playersArray.push([p1,p2,p3,p4])
    }
    return playersArray
  }
  const grpScenario4 = (tplayers) => {
    const ngroups = tplayers.length / 4
    const end = tplayers.length - 1
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      if (i % 2 === 0) {
        const p1 = tplayers[i*2+0];         p1.team = 'A1'; p1.grp = i + 1
        const p2 = tplayers[end - i*2];     p2.team = 'A2'; p2.grp = i + 1
        const p3 = tplayers[i*2+1];         p3.team = 'B1'; p3.grp = i + 1
        const p4 = tplayers[end - i*2 - 1]; p4.team = 'B2'; p4.grp = i + 1
        const ahcap = getHcap(p1, p2)
        const bhcap = getHcap(p3, p4)
        p1.hcap = ahcap
        p2.hcap = ahcap - bhcap
        p4.hcap = bhcap
        playersArray.push([p1,p2,p3,p4])
      } else {
        const p1 = tplayers[i*2+1];         p1.team = 'A1'; p1.grp = i + 1
        const p2 = tplayers[end - i*2 - 1]; p2.team = 'A2'; p2.grp = i + 1
        const p3 = tplayers[i*2+0];         p3.team = 'B1'; p3.grp = i + 1
        const p4 = tplayers[end - i*2];     p4.team = 'B2'; p4.grp = i + 1
        const ahcap = getHcap(p1, p2)
        const bhcap = getHcap(p3, p4)
        p1.hcap = ahcap
        p2.hcap = ahcap - bhcap
        p4.hcap = bhcap
        playersArray.push([p1,p2,p3,p4])
      }
    }
    return playersArray
  }
  const grpScenario5 = (tplayers) => {
    console.log(`-fn-grpScenario5`, tplayers.map(p => p.handicap))
    const ngroups = tplayers.length / 4
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      const g = tplayers.slice(i*4, (i + 1)*4)
      // console.table(g)
      const p1 = g[0]; p1.team = 'A1'; p1.grp = i + 1;
      const p2 = g[2]; p2.team = 'A2'; p2.grp = i + 1;
      const p3 = g[1]; p3.team = 'B1'; p3.grp = i + 1;
      const p4 = g[3]; p4.team = 'B2'; p4.grp = i + 1;
      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      p1.hcap = ahcap
      p2.hcap = ahcap - bhcap
      p4.hcap = bhcap
      playersArray.push(g)
    }
    return playersArray
  }
  const grpScenario6 = (tplayers) => {
    console.log(`-fn-grpScenario6`, tplayers.map(p => p.handicap))
    const ngroups = tplayers.length / 4
    const playersArray = []
    for (let i = 0; i < ngroups; i++) {
      const g = tplayers.slice(i*4, (i + 1)*4)
      // console.table(g)
      const p1 = g[0]; p1.team = 'A1'; p1.grp = i + 1;
      const p2 = g[1]; p2.team = 'A2'; p2.grp = i + 1;
      const p3 = g[2]; p3.team = 'B1'; p3.grp = i + 1;
      const p4 = g[3]; p4.team = 'B2'; p4.grp = i + 1;
      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      p1.hcap = ahcap
      p2.hcap = ahcap - bhcap
      p4.hcap = bhcap
      playersArray.push(g)
    }
    return playersArray
  }
  const allPairs = (teamPlayers) => {
    let pairs = []
    for (let i = 0; i < teamPlayers.length; i++) {
      let a = teamPlayers[i]
      let j = 1
      for (j = i + j; j < teamPlayers.length; j++) {
        let b = teamPlayers[j]
        pairs.push([a, b])
      }
    }
    return pairs
  }
  const allGroups = (pairsA, pairsB) => {
    // let teamdif = 0.2 * 1999
    // let groupn = 4
    let selgrps = []
    let selpids = []
    for (let i = 0; i < pairsA.length; i++) {
      let a = pairsA[i]
      let j = 0
      for (j = 0; j < pairsB.length; j++) {
        let b = pairsB[j]
        let ga = a[0].handicap + a[1].handicap
        let gb = b[0].handicap + b[1].handicap
        let ab = parseFloat((ga - gb).toFixed(6))
        let pi1 = a[0].player_id
        let pi2 = a[1].player_id
        let pi3 = b[0].player_id
        let pi4 = b[1].player_id
        selpids.push(pi1, pi2, pi3, pi4)
        selgrps.push([a[0], a[1], b[0], b[1], ga, gb, ab, [i, j]])
      }
    }
    // console.table(selgrps.map(p => [p[0]]))
    let sortgrps = selgrps.sort((a, b) => { return Math.abs(a[6]) - Math.abs(b[6]) })
    // showgrps(sortgrps)
    return sortgrps
  }
  // const showgrps = (gs) => {
  //   gs.forEach((g) => {
  //     let p1 = g[0]
  //     let p2 = g[1]
  //     let p3 = g[2]
  //     let p4 = g[3]
  //     console.log(g)
  //     console.table([p1.handicap + ' ' + p1.name, p2.handicap + ' ' + p2.name, p3.handicap + ' ' + p3.name, p4.handicap + ' ' + p4.name])
  //   })
  // }
  // const showij = (gs) => {
  //   gs.forEach((g, k) => {
  //     let p1 = g[0]
  //     let p2 = g[1]
  //     let p3 = g[2]
  //     let p4 = g[3]
  //     let pa = parseFloat(g[4].toFixed(1))
  //     let pb = parseFloat(g[5].toFixed(1))
  //     let ab = parseFloat(g[6].toFixed(1))
  //     let i = g[7][0]
  //     let j = g[7][1]
  //     // console.log(i+1, pa, pb, ab + ' ~ ' + p1.name + '/' + p2.name + ' vs ' + p3.name + '/' + p4.name)
  //     // console.table([[pa, pb, ab, i, j], p1.handicap + ' ' + p1.name, p2.handicap + ' ' + p2.name, p3.handicap + ' ' + p3.name, p4.handicap + ' ' + p4.name])
  //     console.log(k+1, i, j, g[6])
  //   })
  // }
  const bestMatch = (sortg) => {
    let selpids = []
    let bm = []
    for (let k = 0; k < sortg.length; k++) {
      let gk = sortg[k]
      let pi1 = gk[0].player_id
      let pi2 = gk[1].player_id
      let pi3 = gk[2].player_id
      let pi4 = gk[3].player_id
      if (selpids.indexOf(pi1)>=0 || selpids.indexOf(pi2)>=0 || selpids.indexOf(pi3)>=0 || selpids.indexOf(pi4)>=0) continue
      selpids.push(pi1, pi2, pi3, pi4)
      bm.push(gk)
    }
    return bm
  }
  const getTeamGroups = (gs) => {
    // return console.log(`groups.length = ${gs.length}`, gs[0][0].team)
    const playersArray = []
    for (let k = 0; k < gs.length; k++) {
      let gk = gs[k]
      let p1 = gk[0]
      let p2 = gk[1]
      let p3 = gk[2]
      let p4 = gk[3]
      // showgrps(gk)
      // console.log(gk)
      p1.team = 'A1'
      p1.grp = k + 1
      // console.log(p1)
      p2.team = 'A2'
      p2.grp = k + 1
      p3.team = 'B1'
      p3.grp = k + 1
      p4.team = 'B2'
      p4.grp = k + 1
      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      const abdif = ahcap - bhcap
      p1.hcap = ahcap
      p2.hcap = abdif
      p4.hcap = bhcap
      // console.table(p1)
      playersArray.push([p1,p2,p3,p4])
    }
    return playersArray
  }
  const grpScenario8 = (tplayers) => {
    var part = new Partition(tplayers)
    var bestp = part.bestPartition()
    // console.log(`bestPartition`, bestp)
    const pp1 = bestp[0]
    const pp2 = bestp[1]
    // var px1 = bestp[2].sort((a, b) => a.handicap < b.handicap ? -1 : 1)
    // var px2 = bestp[3].sort((a, b) => a.handicap < b.handicap ? -1 : 1)
    console.log(pp1)
    console.log(pp2)

    let sbpA = bestp[2].sort((a, b) => { return a.handicap - b.handicap })
    let sbpB = bestp[3].sort((a, b) => { return a.handicap - b.handicap })
    // console.table(sbpA.map(a => a.handicap + ' ~ ' + a.name))
    // console.table(sbpB.map(a => a.handicap + ' ~ ' + a.name))
    let pairsA = allPairs(sbpA)
    let pairsB = allPairs(sbpB)
    // console.table(pairsA.map(a => a[0].handicap + '/' + a[1].handicap + ', ' + a[0].name + '/' + a[1].name))
    // console.table(pairsB.map(a => a[0].handicap + '/' + a[1].handicap + ', ' + a[0].name + '/' + a[1].name))
    let allg = allGroups(pairsA, pairsB)
    let bestgs = bestMatch(allg)
    // showgrps(bestgs)
    // showij(bestgs)
    let teamGroups = getTeamGroups(bestgs.slice(0, tplayers.length/4))
    return teamGroups
  }
  const grpScenario9 = (tplayers) => {
    var part = new Partition(tplayers)
    var pp = part.bestPartition()
    // console.log(`-fn-grpScenario9`, tplayers.map(p => p.handicap))
    // var sortdArr = tplayers.sort((a, b) => parseFloat(a.handicap) < parseFloat(b.handicap) ? -1 : 1)
    // const pp1 = pp[0]
    const pp2 = pp[1]
    var px1 = pp[2].sort((a, b) => parseFloat(a.handicap) < parseFloat(b.handicap) ? -1 : 1)
    var px2 = pp[3].sort((a, b) => parseFloat(a.handicap) < parseFloat(b.handicap) ? -1 : 1)
    console.log('px1:', px1)
    console.log(pp2)
    // console.table(px1)
    // console.table(px2)
    // const pa = pp1.reduce((a, b) => a + b, 0) / pp1.length
    // const pb = pp2.reduce((a, b) => a + b, 0) / pp2.length
    // console.log(`===cHK=== pa=${pa} pb=${pb}`)

    const ngroups = tplayers.length / 4
    const playersArray = []
    for (var i = 0; i < ngroups; i++) {
      var p1 = px1[i*2 + 0]
      var p2 = px1[i*2 + 1]
      var p3 = px2[i*2 + 0]
      var p4 = px2[i*2 + 1]

      p1.team = 'A1'
      p2.team = 'A2'
      p3.team = 'B1'
      p4.team = 'B2'
      p1.grp = i + 1
      p2.grp = i + 1
      p3.grp = i + 1
      p4.grp = i + 1

      const ahcap = getHcap(p1, p2)
      const bhcap = getHcap(p3, p4)
      const abdif = ahcap - bhcap
      p1.hcap = ahcap
      p2.hcap = abdif
      p4.hcap = bhcap
      // console.table(p1)
      playersArray.push([p1,p2,p3,p4])
    }
    return playersArray
  }
  return {
    getABS,
    getHcap,
    grpScenario,
    grpScenario8,
  }
}
