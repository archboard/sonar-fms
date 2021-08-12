import CardSectionHeader from '@/components/CardSectionHeader'
import HelpText from '@/components/HelpText'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import InputWrap from '@/components/forms/InputWrap'
import Label from '@/components/forms/Label'
import MapField from '@/components/forms/MapField'
import Select from '@/components/forms/Select'
import Input from '@/components/forms/Input'
import Fieldset from '@/components/forms/Fieldset'
import CurrencyInput from '@/components/forms/CurrencyInput'
import Button from '@/components/Button'
import AddThingButton from '@/components/forms/AddThingButton'
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
