export default class AlbumModel {
	constructor() {
	}
	swapValue(value) {
		let result;
		let y = "Oui";
		let n = "Non";
		if(value === y) {
			result = n;
		} else {
			result = y;
		}
		return result;
	}
	booleanValue(value) {
		let result;
		let y = "Oui";
		let n = "Non";
		if(value === y) {
			result = 1;
		} else {
			result = 0;
		}
		return result;
	}
}