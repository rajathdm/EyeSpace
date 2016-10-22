<!-- a-frame //-->
<a-scene embedded>

    <a-assets>
        <a-asset-item id="plyModel" src="{{ $model_ply }}"></a-asset-item>
    </a-assets>

    <a-entity id="camera" position="0 1.6 4">
        <a-camera></a-camera>
    </a-entity>

    <a-entity scene-floor-grid id="scene-floor-grid"></a-entity>

    <a-sky color="#000000"></a-sky>

    <a-entity id="model" scale="{{ $scale }}" position="0 0 0" rotation="-90 0 0" ply-model="src: #plyModel">
        <a-animation
            begin="start-rotation"
            end="stop-rotation"
            attribute="rotation"
            dur="5000"
            to="-90 -360 0"
            easing="linear"
            repeat="indefinite">
        </a-animation>
    </a-entity>

</a-scene>
