export function UserGuideTitles () {
  const manualGrouping = () => {
    let tit = '<div class="row no-wrap">'
    tit += '<div class="q-btn q-btn--round bg-green-8 text-white type="button">'
    tit += '<div class="col items-center justify-center row"><i class="q-icon material-icons">add_circle</i></div>'
    tit += '</div>'
    tit += '<div style="margin:2px 0 0 18px">Manually Grouping</div>'
    tit += '</div>'
    return tit
  }
  function groupingPlayers () {
    let tit = '<div class="row no-wrap">'
    tit += '<div class="q-btn q-btn--round glossy bg-purple-10 type="button">'
    tit += '<div class="text-h5 q-pt-sm">S<div class="text-body1" style="margin:-33px 0 0 24px">x</div></div>'
    tit += '</div>'
    tit += '<div style="padding:2px 0 0 4px">Group Players</div>'
    tit += '<div class="q-btn q-btn--outline q-btn--round text-yellow type="button" style="margin-left:30px">'
    tit += '<div class="col items-center justify-center row"><i class="q-icon material-icons">cancel</i></div>'
    tit += '</div>'
    tit += '<div class="q-btn q-btn--outline q-btn--round text-white bg-green-8 type="button" style="margin-left:20px">'
    tit += '<div class="col items-center justify-center row"><i class="q-icon material-icons">add_circle</i></div>'
    tit += '</div>'
    return tit
  }
  function removePlayer () {
    let tit = '<div class="row no-wrap">'
    tit += '<div class="q-btn q-btn--outline q-btn--round text-yellow type="button">'
    tit += '<div class="col items-center justify-center row"><i class="q-icon material-icons">cancel</i></div>'
    tit += '</div>'
    tit += '<div style="margin:2px 0 0 12px">Remove/add Player</div>'
    tit += '<div class="q-btn q-btn--outline q-btn--round text-white bg-green-8 type="button" style="margin-left:25px">'
    tit += '<div class="col items-center justify-center row"><i class="q-icon material-icons">add_circle</i></div>'
    tit += '</div>'
    tit += '</div>'
    return tit
  }
  function defaultTitle () {
    let tit = '<div class="row no-wrap">'
    tit += '<div style="margin:0 9px 0 -5px">'
    tit += '<div class="round-66 inset-shadow-down"><div style="margin:9px 0 0 -13px">苑</div>'
    tit += '</div></div>' 
    tit += '<div style="margin:15px 0 0 5px">Select Match Players</div>'
    tit += '</div>'
    return tit
  }
  return {
    manualGrouping, groupingPlayers, removePlayer, defaultTitle
  }
}