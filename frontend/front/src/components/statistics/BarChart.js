import { Bar } from 'vue-chartjs'
//const { reactiveProp } = mixins

export default {
  extends: Bar,
//  mixins: [reactiveProp],
  props: {
    chartdata: {
      type: Object,
      default: null
    },
    options: {
      type: Object,
      default: null
    }
  },
  mounted () {
    // this.chartData is created in the mixin.
    // If you want to pass options please create a local options object

    this.renderChart(this.chartdata, this.options)
  }
}