<template>
<div class="text-center text-h4 q-pa-sm text-lime">{{ title }}</div>
<div class="color-picker">
    <div v-for="color in colors" :key="color" class="color-box" :style="{ backgroundColor: color }" @click="changeColor(color)"></div>
</div>
<div class="row" style="margin:0 0 0 30px">
  <canvas id="canvas" @mousedown="startPainting" @mouseup="finishedPainting" @mousemove="drawShape" />
  <div class="column items-center q-pl-md" style="height:350px">
    <div class="q-pt-sm"><q-btn outline round color="lime" icon="gesture"   @click="shape='draw'" /></div>
    <div class="q-pt-sm"><q-btn outline round color="cyan" icon="rectangle" @click="shape='rectangle'" /></div>
    <div class="q-pt-sm"><q-btn outline round color="blue" icon="circle"    @click="shape='circle'"/></div>
    <div class="q-pt-sm"><q-btn outline round color="pink" icon="delete"    @click="clearCanvas"/></div>
  </div>
</div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
const title = "Drawing App"
var painting = ref(false);
var canvas = null
var ctx = null
const colors = ["#000000", "#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FF00FF", "#00FFFF"]
const compX = 60
const compY = 50
var p0 = { x: 0, y: 0 }
var p1 = { x: 0, y: 0 }
var p2 = { x: 0, y: 0 }
var p3 = { x: 0, y: 0 }
const px = { x: 0, y: 0 }
var shape = ref('draw')

onMounted(() => {
  canvas = document.getElementById("canvas");
  ctx = canvas.getContext("2d");

  // Set default stroke color
  ctx.strokeStyle = colors[1];

  // Resize canvas
  canvas.height = window.innerHeight * 0.6;
  canvas.width = window.innerWidth * 0.8;

  ctx.lineWidth = 2;
  ctx.lineCap = "round";
})
// ======= shape drawing ========
const changeColor = (color) => {
  ctx.strokeStyle = color;
};
function clearPoints () {
  p0 = px
  p1 = px
  p2 = px
  p3 = px
}
const clearCanvas = () => {
  // clearPoints()
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}
const XclearLine = (p1, p2) => {
  ctx.clearRect(p1.x, p1.y+1, p2.x, p2.y-1);
}

const startPainting = (e) => {
  painting.value = true;
  ctx.beginPath();
  if (shape.value === 'rectangle') p0 = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
}

const finishedPainting = (e) => {
  painting.value = false
  if (shape.value === 'rectangle') {
    p3 = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
    // clearLine(p0, p1)
    // clearLine(p0, p2)
    // ctx.strokeRect(p0.x, p0.y, p3.x, p3.y)
    // clearCanvas()
    drawLine(p1, p3)
    drawLine(p2, p3)
    // ctx.beginPath()
    // ctx.rect(p0.x, p0.y, p3.x, p3.y)
    // ctx.stroke()
  }
  ctx.beginPath()
  clearPoints()
}

const drawShape = (e) => {
  console.log(`-CK-fn-drawShape shape=${shape.value}`)
  // ctx.beginPath()
  if (shape.value === 'draw') draw(e)
  else if (shape.value === 'rectangle') drawRect(e)
}
function drawLine(p1, p2) {
  ctx.moveTo(p1.x, p1.y)
  ctx.lineTo(p2.x, p2.y)
  ctx.stroke()
}
const drawRect = (e) => {
  if (!painting.value && shape.value != 'rectangle') return;
  // console.log(`clientX=${e.clientX}  offsetLeft=${canvas.offsetLeft} clientY=${e.clientY} offsetTop=${canvas.offsetTop}`);
  p3 = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
  p1 = { x:p3.x, y:p0.y }
  p2 = { x:p0.x, y:p3.y }
  drawLine(p0, p1)
  drawLine(p0, p2)
  // console.log('-CK-event', e)
}
const draw = (e) => {
  if (!painting.value || e == null) return;
  // console.log(`clientX=${e.clientX}  offsetLeft=${canvas.offsetLeft} clientY=${e.clientY} offsetTop=${canvas.offsetTop}`);

  const pt = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
  ctx.lineTo(pt.x, pt.y);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(pt.x, pt.y);
}
  // function drawPoint (beginPt, pt) {
    //   ctx.beginPath(beginPt.x, beginPt.y);
  //   ctx.lineTo(pt.x, pt.y);
  //   ctx.stroke();
  //   ctx.moveTo(pt.x, pt.y);
  //   // ctx.beginPath();
  // }
  // function finishedRect () {
  //   // sep3(ppoint, 0, 0);
  //   // sep3(p0, 0, 0);
  //   // sep3(p3, 0, 0);
    
  //   clearCanvas()
  //   drawRect()
  //   p3 = p0;
  //   ppoint = p0;
  //   painting.value = false;
  //   ctx.beginPath();
  // }
  // function startPoint (e) {
  //   painting.value = true
  //   console.log(`clientX=${e.clientX}  offsetLeft=${canvas.offsetLeft} clientY=${e.clientY} offsetTop=${canvas.offsetTop}`);
  //   p0 = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
  //   ppoint = p0
  // }
  
  // function gep3s (e) {
  //   if (!painting.value || e == null) return
  //   const ddelta = 20
  //   // console.log(`clientX=${e.clientX}  offsetLeft=${canvas.offsetLeft} clientY=${e.clientY} offsetTop=${canvas.offsetTop}`);
  //   p3 = { x:e.clientX - canvas.offsetLeft - compX, y:e.clientY - canvas.offsetTop - compY }
  //   if ((p3.x - ppoint.x) > ddelta && (p3.y - ppoint.y) > ddelta ) {
  //     // setTimeout(() => { clearCanvas() }, 1000)
  //     clearCanvas()
  //     drawRect()
  //     ppoint = p3
  //   }
  //   return
  // }
  
  // function drawRect (e) {
  //   if (!painting.value) return;
  //   startPainting(e)
  //   gep3s(e)
  //   console.log(`   to: (${p3.x}, ${p3.y})` )
  //   console.log(`start: (${p0.x}, ${p0.y})` )
  //   const p1 = p0
  //   const p2 = p3
  //   ctx.lineTo(p1.x, p1.y); ctx.lineTo(p2.x, p1.y); // ctx.stroke()
  //   ctx.lineTo(p2.x, p2.y); ctx.lineTo(p2.x, p2.y); // ctx.stroke()
  //   ctx.lineTo(p1.x, p2.y); ctx.lineTo(p1.x, p2.y); // ctx.stroke()
  //   ctx.lineTo(p1.x, p1.y); ctx.lineTo(p1.x, p1.y);
  //   ctx.stroke()
  // }
</script>
<style>
  canvas {
    display: block;
    background-color: #ffffff;
    border: 2px solid #333;
    border-radius: 5px;
    cursor: crosshair;
    margin: 0 10px 0 0;
  }
  
  .color-box {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin: 0 5px;
    cursor: pointer;
    border-radius: 50%;
  }
  
  .color-picker {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
  }
  
  .clear-button {
    display: block;
    margin: 1rem auto;
    padding: 0.5rem 1rem;
    background-color: teal;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
  }
  
  .clear-button:hover {
    background-color: #444;
  }
</style>