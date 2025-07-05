<template>
  <div class="q-pa-md q-gutter-sm bg-teal-10">
    <!-- <h3 class="text-center">BMI Formula</h3> -->
    <h3 class="text-center text-bold text-cyan-2">BMI 计 算 公 式</h3>
    <!-- KaTeX-rendered formula -->
    <div class="text-h4 text-cyan-1 text-center" ref="bmiFormula"></div>

    <!-- Interactive calculator -->
    <div class="calculator text-h6 text-center">
      <input v-model.number="weight" placeholder="Weight (kg)" type="number" style="width:130px" />
      <input v-model.number="height" placeholder="Height (m)" type="number"  style="width:130px" />
      <button @click="calculateBMI">Calculate</button>
      <p v-if="bmi" class="q-pl-sm text-cyan-2 text-h5">BMI: {{ bmi }} ({{ classification }})</p>
    </div>

    <div class="text-h4 text-cyan-1 text-center" ref="bmiFormulaImperialUnits"></div>
    <!-- Interactive calculator -->
    <div class="calculator text-h6 text-center">
      <input v-model.number="weightx" placeholder="Weight (lbs)" type="number" style="width:130px" />
      <input v-model.number="heightx" placeholder="Height (in)" type="number"  style="width:130px" />
      <button @click="calculateBMIx">Calculate</button>
      <p v-if="bmix" class="q-pl-sm text-cyan-2 text-h5">BMI: {{ bmix }} ({{ classification }})</p>
    </div>

    <hr />
    <div class="q-pl-xl text-white text-h5">Below 18.5 : Userweight</div>
    <div class="q-pl-xl text-white text-h5">18.6 ~ 24.9 : Normal weight</div>
    <div class="q-pl-xl text-white text-h5">25.0 ~ 29.9 : Overweight</div>
    <div class="q-pl-xl text-white text-h5">Above 30.0 : Obesity</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import katex from 'katex';
import 'katex/dist/katex.min.css';

const weight = ref(68.13);
const height = ref(1.73);
const weightx = ref(150.2);
const heightx = ref(68.11);
const bmi = ref(null);
const bmix = ref(null);
const classification = ref('');
const bmiFormula = ref(null);
const bmiFormulaImperialUnits = ref(null);
const opened = ref(false);

// Render LaTeX formula on mount
onMounted(() => {
  katex.render(
    // "BMI = \\frac{\\text{Weight (kg)}}{\\text{Height (m)}^2}",
    "BMI = \\frac{\\text{体重 (公斤)}}{\\text{身高 (米)}^2}",
    bmiFormula.value,
    { throwOnError: false }
  );
  katex.render(
    "BMI = \\frac{\\text{Weight (lbs)}}{\\text{Height (in)}^2} \\times 703",
    bmiFormulaImperialUnits.value,
    { throwOnError: false }
  );
});

// Calculate BMI
const calculateBMI = () => {
  const bmiValue = weight.value / (height.value * height.value);
  bmi.value = bmiValue.toFixed(1);

  if (bmi.value < 18.5) classification.value = "Underweight";
  else if (bmi.value < 25) classification.value = "Normal weight";
  else if (bmi.value < 30) classification.value = "Overweight";
  else classification.value = "Obesity";
}

const calculateBMIx = () => {
  const bmiValue = weightx.value / (heightx.value * heightx.value) * 703;
  bmix.value = bmiValue.toFixed(1);

  if (bmix.value < 18.5) classification.value = "Underweight";
  else if (bmix.value < 25) classification.value = "Normal weight";
  else if (bmix.value < 30) classification.value = "Overweight";
  else classification.value = "Obesity";
};
</script>

<style>
.calculator {
  margin-top: 20px;
}
input {
  margin: 5px;
}
</style>
