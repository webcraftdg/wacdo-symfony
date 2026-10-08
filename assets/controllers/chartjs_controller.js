import { Controller } from '@hotwired/stimulus';
import Chart from 'chart.js/auto';

export default class extends Controller {

    static values = {
        labels: Array,
        datasets: Array
    }

    connect() {
        console.log('initialisation chertjs', this.element);
        this.chart = new Chart(this.element, {
            type: 'pie',
            data: {
                labels: this.labelsValue,
                datasets: this.datasetsValue
            }
        });
    }

    disconnect() {
        this.chart?.destroy();
    }
}
