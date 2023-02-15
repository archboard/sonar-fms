import { PlusSmIcon } from '@heroicons/vue/solid'
import { TrashIcon } from '@heroicons/vue/outline'
import CardSectionHeader from '@/components/CardSectionHeader.vue'
import HelpText from '@/components/HelpText.vue'
import Error from '@/components/forms/Error.vue'
import FadeInGroup from '@/components/transitions/FadeInGroup.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import Label from '@/components/forms/Label.vue'
import InputWrap from '@/components/forms/InputWrap.vue'
import Fieldset from '@/components/forms/Fieldset.vue'
import Select from '@/components/forms/Select.vue'
import Input from '@/components/forms/Input.vue'
import CurrencyInput from '@/components/forms/CurrencyInput.vue'
import Button from '@/components/Button.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'

export default {
  components: {
    Label,
    FadeIn,
    Button,
    CurrencyInput,
    InputWrap,
    CardPadding,
    CardWrapper,
    FadeInGroup,
    Error,
    HelpText,
    CardSectionHeader,
    PlusSmIcon,
    TrashIcon,
    Select,
    Input,
    Fieldset,
  },
  props: {
    modelValue: Array,
    form: Object,
  },
  emits: ['update:modelValue'],
}
