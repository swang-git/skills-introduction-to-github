// import { ref } from 'vue'
export class Partition {
  numArray = []
  p1pat = []
  p2pat = []
  p1arr = []
  p2arr = []
  p1sum = 0
  p2sum = 0
  currDiff = Math.abs(this.p1sum - this.p2sum)
  constructor(objArray) {
    this.objArray = objArray.sort((a, b) => a.handicap - b.handicap)
    this.numArray = this.objArray.map(p => p.handicap)
  }
  partitionWithPairMinMatch() {
    // first sort the array - O(nlgn)
    // arr.sort((a, b) => a < b ? -1 : 1)

    // create index table to manage largest unused and smallest unused items
    // O(n) space and O(nlgn) time to build and query the set
    var unused = [] // indies of arr
    for(let a = 0; a < this.numArray.length; a++) unused.push(a)
    // console.log('==AAAA unused', unused)

    var i = 0
    var j = this.numArray.length - 1
    // var part1Sum = 0
    // var part2Sum = 0
    // var diffSum = 0

    //O(n^2) processing time
    while(unused.length > 0) {
      i = unused.at(0)  //first unused item
      j = unused.at(-1)   //last unused item
      // console.log(`ABBA unused i=${i} j=${j}`, unused)
      // diffSum = part1Sum - part2Sum
      // console.log(`-dif- part1Sum=${part1Sum}-part2Sum=${part2Sum}==diffSum=${diffSum}`)
      // console.log(`=======DIFF===${unused.length} ${part1Sum}-${part2Sum}==${diffSum}`)

      //in case of size of the array is not multiple of 4 then we need to process last 3(or 2 or 1)
      //element to assign partition. This is special case handling
      if (unused.length < 4) {
        switch (unused.length) {
          case 1:
            //put the 1 remaining item into smaller partition
            if (this.p1sum > this.p2sum) {
              this.p2pat.push(this.numArray[i])
              this.p2sum += this.numArray[i]
              this.p2arr.push(this.objArray[i])
            } else {
              this.p1pat.push(this.numArray[i])
              this.p1sum += this.numArray[i]
              this.p1arr.push(this.objArray[i])
            }
          break
          case 2:
            //among the remaining 2 put the max in smaller and min in larger bucket
            var maxi, mini
            var max = Math.max(this.numArray[i], this.numArray[j])
            if (max == this.numArray[i]) maxi = i
            else maxi = j
            var min = Math.min(this.numArray[i], this.numArray[j])
            if (min == this.numArray[i]) mini = i
            else mini = j
            // var max = Math.max(this.numArray[i], this.numArray[j])
            // const maxi = max == this.numArray[i] ? i : j
            // var min = Math.min(this.numArray[i], this.numArray[j])
            // const mini = min == this.numArray[i] ? i : j

            if (this.p1sum > this.p2sum) {
              this.p2pat.push(max)
              this.p1pat.push(min)
              this.p2sum += max
              this.p1sum += min
              this.p2arr.push(this.objArray[maxi])
              this.p1arr.push(this.objArray[mini])
            } else {
              this.p1pat.push(max)
              this.p2pat.push(min)
              this.p1um += max
              this.p2sum += min
              this.p1arr.push(this.objArray[maxi])
              this.p2arr.push(this.objArray[mini])
            }
          break
          case 3:
            //among the remaining 3 put the two having total value greater then the third one into smaller partition
            //and the 3rd one to larger bucket
            // unused.remove(i)
            // unused.remove(j)
            unused = unused.filter(a => a != i).filter(a => a != j)
            // unused.splice(j, 1)
            var middle = unused.at(0)
            //
            if (this.p1sum > this.p2sum) {
              if (this.numArray[i] + this.numArray[middle] > this.numArray[j]) {
                this.p2pat.push(this.numArray[i])
                this.p2pat.push(this.numArray[middle])
                this.p1pat.push(this.numArray[j]);
                this.p2sum += this.numArray[i]+this.numArray[middle]
                this.p1sum += this.numArray[j];
                this.p2arr.push(this.objArray[i])
                this.p2arr.push(this.objArray[middle])
                this.p1arr.push(this.objArray[j]);
              } else {
                this.p2pat.push(this.numArray[j])
                this.p1pat.push(this.numArray[i])
                this.p1pat.push(this.numArray[middle])
                this.p1sum += this.numArray[i] + this.numArray[middle]
                this.p2sum += this.numArray[j];
                this.p2arr.push(this.objArray[j])
                this.p1arr.push(this.objArray[i])
                this.p1arr.push(this.objArray[middle])
              }
            } else {
              if (this.numArray[i] + this.numArray[middle] > this.numArray[j]) {
                this.p1pat.push(this.numArray[i])
                this.p1pat.push(this.numArray[middle])
                this.p2pat.push(this.numArray[j])
                this.p1sum += this.numArray[i] + this.numArray[middle]
                this.p2sum += this.numArray[j]
                this.p1arr.push(this.objArray[i])
                this.p1arr.push(this.objArray[middle])
                this.p2arr.push(this.objArray[j])
              } else {
                this.p1pat.push(this.numArray[j])
                this.p2pat.push(this.numArray[i])
                this.p2pat.push(this.numArray[middle])
                this.p2sum += this.numArray[i]+ this.numArray[middle]
                this.p1sum += this.numArray[j]
                this.p1arr.push(this.objArray[j])
                this.p2arr.push(this.objArray[i])
                this.p2arr.push(this.objArray[middle])
              }
            }
          break
          default:
        }
        // diffSum = part1Sum - part2Sum
        break
      }
      // console.table(this.p1pat)

      //first take the largest and the smallest element to create a pair to be inserted into a partition
      //we do this for having a balanced distribute of the numbers in the partitions
      //add pair (i, j) to the smaller partition
      var pairSum = this.numArray[i] + this.numArray[j]
      // var partition = diffSum > 0 ? 2 : 1
      if (this.p1sum < this.p2sum) {
        this.p1pat.push(this.numArray[i])
        this.p1pat.push(this.numArray[j])
        this.p1sum += pairSum
        this.p1arr.push(this.objArray[i])
        this.p1arr.push(this.objArray[j])
      } else {
        this.p2pat.push(this.numArray[i])
        this.p2pat.push(this.numArray[j])
        this.p2sum += pairSum
        this.p2arr.push(this.objArray[i])
        this.p2arr.push(this.objArray[j])
      }
      // console.log(`BAAB unused i=${i} j=${j}`, unused, this.p1pat, this.p2pat)

      // update diff
      //diffSum = part1Sum - part2Sum
      // we have used pair (i, j)
      // unused.remove(i)
      // unused.remove(j)
      unused = unused.filter(a => a != i).filter(b => b != j)
      // console.log(`BBBB unused i=${i} j=${j}`, unused, partition1, partition2)
      //move j to next big element to the left
      j = unused.at(unused.length - 1)
      // now find the buddy for j to be paired with such that sum of them is as close as to pairSum
      // so we will find such buddy A[k], i<=k<j such that value of ((A[j]+A[k])-pairSum) is minimized.
      var buddyIndex = unused.at(0)
      // var buddyIndex = 0
      var minPairSumDiff = Number.MAX_SAFE_INTEGER
      for(let k = buddyIndex; k<j; k++){
        if (!unused.includes(k)) continue

        var compPairSum = this.numArray[j] + this.numArray[k]
        var pairSumDiff = Math.abs(pairSum - compPairSum)

        if (pairSumDiff < minPairSumDiff) {
          minPairSumDiff = pairSumDiff
          buddyIndex = k
        }
      }

      //we now find buddy for j. So we add pair (j,buddyIndex) to the other partition
      if (j != buddyIndex) {
        pairSum = this.numArray[j] + this.numArray[buddyIndex]
        if (this.p1sum < this.p2sum) {
          this.p1pat.push(this.numArray[j])
          this.p1pat.push(this.numArray[buddyIndex])
          this.p1sum += pairSum
          this.p1arr.push(this.objArray[j])
          this.p1arr.push(this.objArray[buddyIndex])
        } else {
          this.p2pat.push(this.numArray[j]);
          this.p2pat.push(this.numArray[buddyIndex])
          this.p2sum += pairSum;
          this.p2arr.push(this.objArray[j]);
          this.p2arr.push(this.objArray[buddyIndex])
        }

        // we have used pair (j, buddyIndex)
        // unused.remove(j);
        // unused.remove(buddyIndex)
        unused = unused.filter(a => a != j).filter(b => b != buddyIndex)
        // console.log(`CCCC unused j=${j} buddyIndex=${buddyIndex} arrj=${this.numArray[j]} arrbuddyIdx=${this.numArray[buddyIndex]}`, unused, this.p1pat, this.p2pat)
      }
    }
  }
  swap12(smli, bigj) {
    // find 2 numbers from partitions with big sum smal sum respectively
    // and swap them if they make difference of sums smaller
    // this.currDiff = this.p1sum - this.p2sum
    // console.log(`--fn-A-swapping12 p1sum=${this.p1sum} > p2sum=${this.p2sum} currDiff=${this.currDiff}`)
    for (let i = smli; i < this.p2pat.length; i++) {
      for (let j = bigj; j>=0; j--) {
        var big = this.p1pat[j]
        var sml = this.p2pat[i]
        if (big <= sml) continue // no point to swap a smaller number from smlSumPat -- partition with smaller sum
        // var newDiff = Math.abs(currDiff - 2*Math.abs(big - sml))
        var newDiff = Math.abs(this.currDiff - 2*(big - sml))
        if (newDiff < this.currDiff) {
          // console.log(`--CK1--swapping12 i=${i} j=${j} sml=${sml} big=${big} newDiff=${newDiff} currDiff=${this.currDiff}`, this.p1pat, this.p2pat)
          // console.log(`--doSwapping12 i=${i} j=${j} sml=${sml} big=${big} newDiff=${newDiff.toFixed(2)} currDiff=${this.currDiff.toFixed(2)}`)
          this.p2pat.splice(i, 1, big)
          this.p1pat.splice(j, 1, sml)
          var bigO = this.p1arr[j]
          var smlO = this.p2arr[i]
          this.p2arr.splice(i, 1, bigO)
          this.p1arr.splice(j, 1, smlO)
          // console.log(`--CK2--swapping12 i=${i} j=${j} sml=${sml} big=${big}`, this.p1pat, this.p2pat)
          this.p1sum = this.p1pat.reduce((a, b) => a + b, 0)
          this.p2sum = this.p2pat.reduce((a, b) => a + b, 0)
          this.currDiff = Math.abs(this.p1sum - this.p2sum)
          if (this.p1sum > this.p2sum) this.swap12(i, j)
          if (this.p1sum < this.p2sum) this.swap21(i, j)
        }
      }
    }
  }
  swap21(smli, bigj) {
    // console.log(`--fn-A-swapping21 p1sum=${this.p1sum} < p2sum=${this.p2sum}`)
    // var currDiff = this.p2sum - this.p1sum
    for (let i = smli; i < this.p1pat.length; i++) {
      for (let j = bigj; j>=0; j--) {
        var big = this.p2pat[j]
        var sml = this.p1pat[i]
        if (big <= sml) continue // no point to swap a smaller number from smlSumPat -- partition with smaller sum
        // var newDiff = Math.abs(currDiff - 2*Math.abs(big - sml))
        var newDiff = Math.abs(this.currDiff - 2*(big - sml))
        if (newDiff < this.currDiff) {
          // console.log(`--doSwapping21 i=${i} j=${j} sml=${sml} big=${big} newDiff=${newDiff.toFixed(2)} currDiff=${this.currDiff.toFixed(2)}`)
          this.p1pat.splice(i, 1, big)
          this.p2pat.splice(j, 1, sml)
          var bigO = this.p2arr[j]
          var smlO = this.p1arr[i]
          this.p1arr.splice(i, 1, bigO)
          this.p2arr.splice(j, 1, smlO)
          this.p1sum = this.p1pat.reduce((a, b) => a + b, 0)
          this.p2sum = this.p2pat.reduce((a, b) => a + b, 0)
          this.currDiff = Math.abs(this.p1sum - this.p2sum)
          if (this.p1sum > this.p2sum) this.swap12(i, j)
          if (this.p1sum < this.p2sum) this.swap21(i, j)
        }
      }
    }
  }
  bestPartition() { // partition an array into 2 arrays with the same length such that the difference between sums of the two arrays minimized
    // console.table(this.p1pat)
    this.partitionWithPairMinMatch()
    // console.log(`-fn-bestPartition`, this.numArray)
    this.currDiff = Math.abs(this.p1sum - this.p2sum)
    if (this.p1sum > this.p2sum) this.swap12(0, this.p1pat.length-1)
    else if (this.p1sum < this.p2sum) this.swap21(0, this.p2pat.length-1)
    // console.log(`-fn-bestPartition p1sum=${this.p1sum} p2sum=${this.p2sum}`, this.p1pat, this.p2pat)
    return [this.p1pat, this.p2pat, this.p1arr, this.p2arr]
  }
}
