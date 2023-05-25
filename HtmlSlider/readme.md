# Html Slider

Classe permettant d’insérer un slider dans une page html. Le slider faisant reference a un `input[type='range]` mais avec deux points.

## Exemple

Ci-dessous un exemple d'utilisation.

```html
<label for="bloc" class="form-label mb-3">Slider</label>
<div class="slider mb-3" id="bloc">
    <div class="track"></div>
    <div class="range"></div>
    <div class="thumb left" draggable="true"></div>
    <div class="thumb right" draggable="true"></div>
    <div class="value left">A</div>
    <div class="value right">E</div>
</div>

<script>
    let tab = ['A', 'B', 'C', 'D', 'E'];
    let nums = Array.from({length: 50}, (_, i) => i + 1);

    let mySlider1 = new PhenixSlider(
        [document.querySelector('#bloc > .thumb.left'), document.querySelector('#bloc > .thumb.right')],
        document.querySelector('#bloc > .range'), tab,
        [document.querySelector('#bloc > .value.left'), document.querySelector('#bloc >.value.right')],
        document.querySelector('#blox')
    );
    mySlider1.activate();
</script>
```

[Signalez nous](https://github.com/Asmitta-01/Helpers/issues) si vous avez une pre-occupation.
