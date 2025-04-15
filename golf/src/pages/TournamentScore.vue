<template lang="html">
  <div class="players-ranking" style="font-size: 18px; background: rgb(28, 78, 68); color: white" >
    <div style=" text-align: center; font-size: 18px; background: teal; color: white; padding: 3px 0 0 0; " >{{ gameName }}</div>
    <q-input clearable v-model="search" type="search" dark>
      <template v-slot:append>
        <q-icon name="search" />
      </template>
    </q-input>
    <q-select radio dark color="cyan" v-model="sortby" @change="doSorting" :options="sortOptions" float-label="Select for Score / Ranking / Index" hide-underline />
    <div v-for="p in pRanks" :key="p.k" style="background: rgb(28, 68, 68)">
      <q-expansion-item dark :label="p.label + ' ~ ' + p.player">
        <q-card class="bg-teal-9">
          <q-card-section>
            <div v-html="scoreDetails(p)" />
          </q-card-section>
        </q-card>
      </q-expansion-item>
    </div>
  </div>
</template>
<script setup>
import { libFunctions } from 'src/composables/libFunctions';
const { store, $router } = libFunctions()
import { axiosFunctions } from 'src/composables/axiosFunctions';
const { gaxios } = axiosFunctions()
const gameName = "";
const search = "";
const playersRanking = [];
const sortby = { value: "GSC", label: "Sort by Gross Score" };
const sortOptions = [];

console.log("-ST-TournamentScore");
const tid = store.tournament.id;

if (tid < 0 || tid === undefined) {
  $router.push({ path: "TournamentList" });
}

const path = process.env.API + "/golf/PlayersRanking/" + tid;
gaxios(path);

let opt = { label: "Gross Score", value: "GSC" };
this.sortOptions.push(opt);
opt = { label: "Gross Ranking", value: "GRK" };
this.sortOptions.push(opt);
opt = { label: "Net Score", value: "NSC" };
this.sortOptions.push(opt);
opt = { label: "Points of the Player of Year", value: "PYP" };
this.sortOptions.push(opt);
opt = { label: "Club Index", value: "CDX" };
this.sortOptions.push(opt);

function doSorting() {
  console.log("-CK-fn-sorted by", this.sortby.value);
  const sortKey = this.sortby.value === "" ? "GSC" : this.sortby.value;
  if (sortKey === "") return playersRanking;

  let data = playersRanking;
  const filterKey = this.search && this.search.toLowerCase();
  if (filterKey) {
    const words = filterKey.split(" ");
    words.forEach((word) => {
      data = data.filter((mem) => {
        return mem.player.toLowerCase().indexOf(word) >= 0;
      });
    });
  }
  const order = sortKey === "PYP" ? -1 : 1;
  let showData = [];
  if (sortKey === "PYP")
    data.forEach((p) => {
      if (p.PYP > 0) showData.push(p);
    });
  else if (sortKey === "GSC")
    data.forEach((p) => {
      if (p.GSC > 0 && p.GSC != null && p.GSC !== undefined) showData.push(p);
    });
  else if (sortKey === "GRK")
    data.forEach((p) => {
      if (p.GRK > 0 && p.GRK != null && p.GRK !== undefined) showData.push(p);
    });
  else if (sortKey === "NSC")
    data.forEach((p) => {
      if (p.NSC > 0 && p.NSC != null && p.NSC !== undefined) showData.push(p);
    });
  else if (sortKey === "CDX")
    data.forEach((p) => {
      if (p.CDX > 0 && p.CDX != null && p.CDX !== undefined) showData.push(p);
    });
  else showData = data;

  showData = showData.slice().sort(function (a, b) {
    a = parseFloat(a[sortKey]);
    b = parseFloat(b[sortKey]);
    return (a === b ? 0 : a > b ? 1 : -1) * order;
  });

  let plabel = this.isDesk ? "Gross Score: " : "G Score: ";
  if (sortKey === "GRK") plabel = this.isDesk ? "Gross Rank: " : "G Rank: ";
  if (sortKey === "NSC") plabel = this.isDesk ? "Net Score: " : "N Score: ";
  if (sortKey === "NRK") plabel = this.isDesk ? "Net Rank: " : "N Rank: ";
  if (sortKey === "CDX") plabel = this.isDesk ? "Club Index: " : "C Index: ";
  if (sortKey === "PYP") plabel = "PoY Points: ";
  showData.forEach((p, i) => {
    p.label = i + 1 + ". " + plabel + p[sortKey];
  });
  console.log("-CK-fn-showData sorted", showData);
  return showData;
}
function scoreDetails(p) {
  let htmlstr = "<ul>";
  if (p.POS !== null && p.POS !== "" && p.POS !== undefined)
    htmlstr += "<li>Tournament Award: " + p.POS + "</li>";
  if (p.GRK !== null) htmlstr += "<li>Gross Rank: " + p.GRK + "</li>";
  if (p.GSC !== null) htmlstr += "<li>Gross Score: " + p.GSC + "</li>";
  htmlstr += "<li>Net Score: " + p.NSC + "</li>";
  if (p.PYP > 0) htmlstr += "<li>PoY Points: " + p.PYP + "</li>";
  if (p.CDX > 0) htmlstr += "<li>Club Index: " + p.CDX + "</li>";
  htmlstr += "</ul>";
  return htmlstr;
}
// const compRanks = computed(() => {
//   return doSorting();
// });
</script>
