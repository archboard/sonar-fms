import CardSectionHeader from '@/components/CardSectionHeader.vue'
import HelpText from '@/components/HelpText.vue'
import FadeInGroup from '@/components/transitions/FadeInGroup.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Label from '@/components/forms/Label.vue'
import MapField from '@/components/forms/MapField.vue'
import Select from '@/components/forms/Select.vue'
import Input from '@/components/forms/Input.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import CurrencyInput from '@/components/forms/CurrencyInput.vue'
import Button from '@/components/Button.vue'
import AddThingButton from '@/components/forms/AddThingButton.vue'
import { TrashIcon } from '@heroicons/vue/outline'

export default {
  components: {
    AddThingButton,
    Button,
    CurrencyInput,
    Input,
    Select,
    MapField,
    InputWrap,
    CardPadding,
    CardWrapper,
    FadeInGroup,
    HelpText,
    CardSectionHeader,
    TrashIcon,
    Fieldset,
    Label,
  },
  emits: ['update:modelValue'],
  props: {
    modelValue: Array,
    form: Object,
    headers: Array,
  }
}
