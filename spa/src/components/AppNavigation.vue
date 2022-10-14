<template>
	<div class="page-wrapper p-t-180 p-b-100">
		<div class="wrapper wrapper--w960">
			<div class="card card-2">
				<div class="card-heading" :style="cardBackground"></div>
				<div class="card-body">
					<h2 class="title">Thông tin người dùng</h2>
					<form @submit="onSubmit">
						<div class="input-group">
							<input
								class="input--style-2"
								type="text"
								placeholder="Name"
								v-model="name"
								name="name"
							/>
						</div>
						<div class="input-group">
							<div class="rs-select2 js-select-simple select--no-search">
								<select v-model="selected" required @change="changeGender">
									<option
										v-for="option in genderOptions"
										:value="option.value"
										:key="option.value"
										>{{ option.text }}</option
									>
								</select>
							</div>
						</div>

						<div class="input-group">
							<input
								class="input--style-2"
								type="number"
								v-model="age"
								required
								placeholder="Tháng tuổi (24 - 240 tháng)"
								name="age"
								min="24"
								max="240"
							/>
						</div>

						<div class="input-group">
							<input
								class="input--style-2"
								type="number"
								v-model="weight"
								required
								placeholder="Cân nặng (10 - 100 kg)"
								name="weight"
								min="10"
								max="101"
							/>
						</div>
						<div class="input-group">
							<input
								class="input--style-2"
								type="number"
								v-model="height"
								required
								placeholder="Chiều cao (79 - 195 cm)"
								name="height"
								min="79"
								max="195"
							/>
						</div>
						<div class="p-t-30">
							<button class="btn btn--radius btn--green" type="submit">
								Search
							</button>
						</div>
					</form>
				</div>
				<div class="card-footer" v-if="showResult">
					<p>
						Chiều cao hiện tại: {{ height }} cm. <span>Chiều cao dự đoán:</span>
						<strong>{{ y_height }}</strong> cm
					</p>
					<p>
						Cân nặng hiện tại: {{ weight }} kg. <span>Cân nặng dự đoán:</span>
						<strong>{{ y_weight }}</strong> kg
					</p>
					<p>BMI: {{ bmi }} - <strong>{{bmiResult}}</strong></p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import axios from "axios";

export default {
	/*global wpData:true*/
	/*eslint no-undef: "error"*/
	data() {
		return {
		  //bgCard: `url("${wpData.template_directory_uri}/assets/images/bg-heading-02.jpg") top left/cover no-repeat`,
      showResult: false,
			personsData: ([] = []),
			selected: 1,
			genderOptions: [
				{ value: 0, text: " Nữ" },
				{ value: 1, text: " Nam" }
			],
			name: "",
			age: "",
			height: "",
			weight: "",
			y_weight: "",
			y_height: "",
			bmi: "",
			y_bmi: "",
      bmiResult: "",
      phone: "",
      email: ""
		};
	},
	mounted() {
		// get posts from the WordPress REST API on component creation.
		this.fetchData();
	},
  computed: {
    cardBackground() {
      return {
        'background': `url("${wpData.template_directory_uri}/assets/images/bg-heading-02.jpg") top left/cover no-repeat`,
      }
    }
  },
	methods: {
		changeGender(value) {},

		async fetchData() {
			const restURL = wpData.rest_url;
			const response = await axios.get(`${restURL}/bmi/v1/all`);
			this.personsData = response.data;
		},

		onSubmit(e) {
			e.preventDefault();
			// if(!this.name){
			//     alert('Please Add a Name')
			//     return
			// }
			const data = this.personsData;
			const dataByGender = data.filter(o => o.gender == this.selected);
			const dataByAge = dataByGender.filter(
				o => o.age >= this.age && o.age < parseFloat(this.age) + 5
			);
			dataByAge.sort(function(a, b) {
				return parseFloat(a.age) - parseFloat(b.age);
			});
			const dataSearch = [...dataByAge];
			const weightInRange = dataSearch.map(o => o.weight);
			const heightInRange = dataSearch.map(o => o.height);
			if (
				this.weight > Math.min(...weightInRange) &&
				this.weight < Math.max(...weightInRange)
			) {
				const dataByWeight = dataSearch.filter(o => o.weight >= this.weight);
				dataByWeight.sort(function(a, b) {
					return parseFloat(a.weight) - parseFloat(b.weight);
				});
				const searchObject = dataByWeight.find(o => o.weight >= this.weight);
				if (searchObject) {
					this.y_weight = searchObject.y_weight;
				}
			} else {
				alert("Cân nặng nằm ngoài khả năng dự doán");
			}
			if (
				this.height > Math.min(...heightInRange) &&
				this.height < Math.max(...heightInRange)
			) {
				const dataByHeight = dataSearch.filter(o => o.height >= this.height);
				dataByHeight.sort(function(a, b) {
					return parseFloat(a.height) - parseFloat(b.height);
				});
				const searchObject = dataByHeight.find(o => o.height >= this.height);
				if (searchObject) {
					this.y_height = searchObject.y_height;
				}
			} else {
				alert("Chiều cao nằm ngoài khả năng dự doán");
			}

			//this.bmi = this.weight/(this.height**2);
			let bmi = (this.weight / ((this.height * this.height) / 10000)).toFixed(
				2
			);
      this.bmi = bmi;
			if (bmi < 18.6) 
        this.bmiResult = `Thiếu cân`;
			else if (bmi >= 18.6 && bmi < 24.9)
        this.bmiResult = `Bình thường`;
			else this.bmiResult = `Dư cân`;

			
      this.showResult = true;

      const form = {
                name: this.name,
                email: this.email,
                phone: this.phone,
                gender: this.selected,
                age: this.age,
                height: this.height,
                weight: this.weight,
                y_height: this.y_height,
                y_weight: this.y_weight,
                bmi: this.bmi
            }
      axios.post(`${wpData.rest_url}/bmi/v1/add`, form)
            .then((res) => {
                //Perform Success Action
            })
            .catch((error) => {
                // error.response.status Check status code
            }).finally(() => {
                //Perform action in always
            });
		}
	}
};
</script>

<style scoped>
.row {
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-flex-wrap: wrap;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
}

.row-space {
  -webkit-box-pack: justify;
  -webkit-justify-content: space-between;
  -moz-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
}

.col-2 {
  width: -webkit-calc((100% - 60px) / 2);
  width: -moz-calc((100% - 60px) / 2);
  width: calc((100% - 60px) / 2);
}

@media (max-width: 767px) {
  .col-2 {
    width: 100%;
  }
}


input[type="date" i] {
  padding: 14px;
}

.table-condensed td, .table-condensed th {
  font-size: 14px;
  font-family: "Roboto", "Arial", "Helvetica Neue", sans-serif;
  font-weight: 400;
}

.daterangepicker td {
  width: 40px;
  height: 30px;
}

.daterangepicker {
  border: none;
  -webkit-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  -moz-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  display: none;
  border: 1px solid #e0e0e0;
  margin-top: 5px;
}

.daterangepicker::after, .daterangepicker::before {
  display: none;
}

.daterangepicker thead tr th {
  padding: 10px 0;
}

.daterangepicker .table-condensed th select {
  border: 1px solid #ccc;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  font-size: 14px;
  padding: 5px;
  outline: none;
}

/* ==========================================================================
   #FORM
   ========================================================================== */
input {
  outline: none;
  margin: 0;
  border: none;
  -webkit-box-shadow: none;
  -moz-box-shadow: none;
  box-shadow: none;
  width: 100%;
  font-size: 14px;
  font-family: inherit;
}

/* input group 1 */
/* end input group 1 */
.input-group {
  position: relative;
  margin-bottom: 32px;
  border-bottom: 1px solid #e5e5e5;
}

.input-icon {
  position: absolute;
  font-size: 18px;
  color: #ccc;
  right: 8px;
  top: 50%;
  -webkit-transform: translateY(-50%);
  -moz-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  -o-transform: translateY(-50%);
  transform: translateY(-50%);
  cursor: pointer;
}

.input--style-2 {
  padding: 9px 0;
  color: #666;
  font-size: 16px;
  font-weight: 500;
}

.input--style-2::-webkit-input-placeholder {
  /* WebKit, Blink, Edge */
  color: #808080;
}

.input--style-2:-moz-placeholder {
  /* Mozilla Firefox 4 to 18 */
  color: #808080;
  opacity: 1;
}

.input--style-2::-moz-placeholder {
  /* Mozilla Firefox 19+ */
  color: #808080;
  opacity: 1;
}

.input--style-2:-ms-input-placeholder {
  /* Internet Explorer 10-11 */
  color: #808080;
}

.input--style-2:-ms-input-placeholder {
  /* Microsoft Edge */
  color: #808080;
}

/* ==========================================================================
   #SELECT2
   ========================================================================== */
.select--no-search .select2-search {
  display: none !important;
}

.rs-select2 .select2-container {
  width: 100% !important;
  outline: none;
}

.rs-select2 .select2-container .select2-selection--single {
  outline: none;
  border: none;
  height: 36px;
}

.rs-select2 .select2-container .select2-selection--single .select2-selection__rendered {
  line-height: 36px;
  padding-left: 0;
  color: #808080;
  font-size: 16px;
  font-family: inherit;
  font-weight: 500;
}

.rs-select2 .select2-container .select2-selection--single .select2-selection__arrow {
  height: 34px;
  right: 4px;
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -moz-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -moz-box-align: center;
  -ms-flex-align: center;
  align-items: center;
}

.rs-select2 .select2-container .select2-selection--single .select2-selection__arrow b {
  display: none;
}

.rs-select2 .select2-container .select2-selection--single .select2-selection__arrow:after {
  font-family: "Material-Design-Iconic-Font";
  content: '\f2f9';
  font-size: 18px;
  color: #ccc;
  -webkit-transition: all 0.4s ease;
  -o-transition: all 0.4s ease;
  -moz-transition: all 0.4s ease;
  transition: all 0.4s ease;
}

.rs-select2 .select2-container.select2-container--open .select2-selection--single .select2-selection__arrow::after {
  -webkit-transform: rotate(-180deg);
  -moz-transform: rotate(-180deg);
  -ms-transform: rotate(-180deg);
  -o-transform: rotate(-180deg);
  transform: rotate(-180deg);
}

.select2-container--open .select2-dropdown--below {
  border: none;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  -webkit-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  -moz-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  border: 1px solid #e0e0e0;
  margin-top: 5px;
  overflow: hidden;
}


.title {
  text-transform: uppercase;
  font-weight: 700;
  margin-bottom: 37px;
}
.page-wrapper {
  min-height: 100vh;
}

.p-t-100 {
  padding-top: 100px;
}

.p-t-180 {
  padding-top: 180px;
}

.p-t-20 {
  padding-top: 20px;
}

.p-t-30 {
  padding-top: 30px;
}

.p-b-100 {
  padding-bottom: 100px;
}

/* ==========================================================================
   #WRAPPER
   ========================================================================== */
.wrapper {
  margin: 0 auto;
}

.wrapper--w960 {
  max-width: 960px;
}

.wrapper--w680 {
  max-width: 680px;
}

.card {
  overflow: hidden;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  background: #fff;
}

.card-2 {
  -webkit-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  -moz-box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
  -webkit-border-radius: 10px;
  -moz-border-radius: 10px;
  border-radius: 10px;
  width: 100%;
  display: table;
}

.card-2 .card-heading {
  width: 29.1%;
  display: table-cell;
}

.card-2 .card-body {
  display: table-cell;
  padding: 80px 90px;
  padding-bottom: 88px;
}

@media (max-width: 767px) {
  .card-2 {
    display: block;
  }
  .card-2 .card-heading {
    width: 100%;
    display: block;
    padding-top: 300px;
    background-position: left center;
  }
  .card-2 .card-body {
    display: block;
    padding: 60px 50px;
  }
}
</style>
