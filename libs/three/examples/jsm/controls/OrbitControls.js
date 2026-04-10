import {
	EventDispatcher,
	MOUSE,
	Quaternion,
	Spherical,
	TOUCH,
	Vector2,
	Vector3
} from '../../../build/three.module.js';

const STATE = {
	NONE: -1,
	ROTATE: 0,
	DOLLY: 1,
	PAN: 2
};

class OrbitControls extends EventDispatcher {

	constructor(object, domElement) {

		super();

		this.object = object;
		this.domElement = domElement;

		this.enabled = true;
		this.target = new Vector3();

		this.minDistance = 0;
		this.maxDistance = Infinity;

		this.enableZoom = true;
		this.zoomSpeed = 1.0;

		this.enableRotate = true;
		this.rotateSpeed = 1.0;

		this.enablePan = true;
		this.panSpeed = 1.0;

		let state = STATE.NONE;

		const scope = this;

		const spherical = new Spherical();
		const sphericalDelta = new Spherical();

		let scale = 1;
		const panOffset = new Vector3();

		this.update = function () {

			const offset = new Vector3();
			const quat = new Quaternion().setFromUnitVectors(object.up, new Vector3(0, 1, 0));
			const quatInverse = quat.clone().invert();

			const position = scope.object.position;

			offset.copy(position).sub(scope.target);
			offset.applyQuaternion(quat);

			spherical.setFromVector3(offset);

			spherical.theta += sphericalDelta.theta;
			spherical.phi += sphericalDelta.phi;

			spherical.makeSafe();

			spherical.radius *= scale;

			scope.target.add(panOffset);

			offset.setFromSpherical(spherical);
			offset.applyQuaternion(quatInverse);

			position.copy(scope.target).add(offset);

			scope.object.lookAt(scope.target);

			sphericalDelta.set(0, 0, 0);
			panOffset.set(0, 0, 0);
			scale = 1;
		};

		this.domElement.addEventListener('wheel', function (event) {
			if (!scope.enableZoom) return;
			scale *= event.deltaY > 0 ? 1.1 : 0.9;
			scope.update();
		});

		this.domElement.addEventListener('mousedown', function (event) {
			state = STATE.ROTATE;
		});

		this.domElement.addEventListener('mouseup', function () {
			state = STATE.NONE;
		});

		this.domElement.addEventListener('mousemove', function (event) {

			if (state !== STATE.ROTATE) return;

			sphericalDelta.theta -= event.movementX * 0.005;
			sphericalDelta.phi -= event.movementY * 0.005;

			scope.update();
		});
	}
}

export { OrbitControls };